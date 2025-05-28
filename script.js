document.addEventListener('DOMContentLoaded', function() {
    // Smooth scrolling for navigation links
    const navLinks = document.querySelectorAll('header nav ul li a[href^="#"]');

    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetElement = document.querySelector(targetId);

            if (targetElement) {
                targetElement.scrollIntoView({
                    behavior: 'smooth'
                });
            }
        });
    });

    // Optional: Basic mobile navigation toggle (if we add a burger menu icon later)
    // For now, this part can be minimal or a placeholder comment,
    // as the current HTML/CSS doesn't include a burger icon for mobile.
    // We can expand this if we enhance the mobile menu design.
    const mobileNavToggle = document.querySelector('.mobile-nav-toggle'); // Assuming a class for a toggle button
    const primaryNav = document.querySelector('header nav ul');

    if (mobileNavToggle && primaryNav) {
        mobileNavToggle.addEventListener('click', () => {
            primaryNav.classList.toggle('nav-active');
            // Add ARIA attributes for accessibility if implementing a toggle
        });
    }

    // Highlight active navigation link based on scroll position (optional advanced feature)
    // This is more complex and can be added later if desired.
    // For now, we can keep it simple.

    // Dashboard Sidebar Navigation (existing logic to be modified)
    const sidebarNav = document.querySelector('.sidebar-nav');
    const mainHeaderTitle = document.querySelector('.main-content .main-header h1');
    // Agregaremos un contenedor para los datos del inventario
    const inventoryDataContainer = document.createElement('div');
    inventoryDataContainer.id = 'inventoryDataContainer';
    // Intentar insertar el contenedor en la sección .dashboard-tables o .main-content
    const dashboardTablesSection = document.querySelector('.dashboard-tables');
    if (sidebarNav) { // Only try to inject if sidebar (hence dashboard) exists
        if (dashboardTablesSection) {
            dashboardTablesSection.innerHTML = ''; // Limpiar placeholder
            dashboardTablesSection.appendChild(inventoryDataContainer);
        } else if (mainHeaderTitle) { // Fallback si .dashboard-tables no está
            // Ensure mainHeaderTitle and its parent structure exist before querySelector
            const mainContent = document.querySelector('.main-content');
            if (mainContent) {
                 const widgetsSection = mainContent.querySelector('.dashboard-widgets');
                 if (widgetsSection) {
                    widgetsSection.insertAdjacentElement('afterend', inventoryDataContainer);
                 } else {
                    // If no widgets, append directly to main content after header
                    mainContent.querySelector('.main-header').insertAdjacentElement('afterend', inventoryDataContainer);
                 }
            }
        }
    }


    if (sidebarNav && mainHeaderTitle) {
        const navLinks = sidebarNav.querySelectorAll('ul li a');
        const navListItems = sidebarNav.querySelectorAll('ul li');

        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                // No prevenir default para el enlace 'Volver a Landing'
                if (this.getAttribute('href') === 'index.html') {
                    // Si hay un token, quizás queramos limpiarlo al volver al landing.
                    // localStorage.removeItem('jwtToken'); // Opcional
                    return; 
                }
                e.preventDefault();

                const linkText = this.textContent.trim();
                mainHeaderTitle.textContent = (linkText === "Resumen") ? "Resumen General" : linkText;

                navListItems.forEach(li => li.classList.remove('active'));
                this.parentElement.classList.add('active');

                // --- NUEVA LÓGICA PARA CARGAR DATOS DEL INVENTARIO ---
                if (linkText === "Inventario") {
                    loadInventoryData();
                } else {
                    // Para otras secciones, por ahora limpiar el contenedor de inventario
                    // y quizás mostrar un mensaje de "Contenido de [sección]..."
                    inventoryDataContainer.innerHTML = `<p>Contenido para ${linkText} se mostrará aquí.</p>`;
                    // Ocultar otros placeholders si es necesario
                    if(document.querySelector('.dashboard-widgets')) document.querySelector('.dashboard-widgets').style.display = 'grid';
                    if(document.querySelector('.chart-placeholder')) document.querySelector('.chart-placeholder').style.display = 'block';
                     // Mostrar widgets y chart placeholder si no estamos en inventario
                }
            });
        });
    }

    function loadInventoryData() {
        const token = localStorage.getItem('jwtToken');
        if (!token) {
            alert('No estás autenticado. Redirigiendo a la página de inicio.');
            window.location.href = 'index.html';
            return;
        }

        // Mostrar un estado de carga
        inventoryDataContainer.innerHTML = '<p>Cargando datos de inventario...</p>';
        // Ocultar otras secciones mientras se carga inventario
        if(document.querySelector('.dashboard-widgets')) document.querySelector('.dashboard-widgets').style.display = 'none';
        if(document.querySelector('.chart-placeholder')) document.querySelector('.chart-placeholder').style.display = 'none';


        fetch('backend/api/inventory.php', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json'
            }
        })
        .then(response => {
            if (response.status === 401) { // No autorizado o token expirado
                localStorage.removeItem('jwtToken');
                alert('Tu sesión ha expirado o el token es inválido. Por favor, inicia sesión de nuevo.');
                window.location.href = 'index.html';
                throw new Error('Token Inválido/Expirado'); // Detener la cadena de promesas
            }
            if (!response.ok) {
                return response.json().then(errData => {
                    throw new Error(errData.message || `Error del servidor: ${response.status}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data.inventory && Array.isArray(data.inventory)) {
                if (data.inventory.length === 0) {
                    inventoryDataContainer.innerHTML = '<p>No hay items en el inventario.</p>';
                    return;
                }

                let tableHTML = '<h3>Inventario Actual</h3><table border="1" style="width:100%; border-collapse: collapse;">';
                tableHTML += '<thead><tr><th>SKU</th><th>Nombre</th><th>Cantidad</th><th>Ubicación</th></tr></thead><tbody>';
                data.inventory.forEach(item => {
                    tableHTML += `<tr>
                        <td>${item.sku || 'N/A'}</td>
                        <td>${item.nombre || 'N/A'}</td>
                        <td>${item.cantidad || 0} ${item.unidad || ''}</td>
                        <td>Pasillo ${item.ubicacion?.pasillo || 'N/A'}, Estantería ${item.ubicacion?.estanteria || 'N/A'}</td>
                    </tr>`;
                });
                tableHTML += '</tbody></table>';
                inventoryDataContainer.innerHTML = tableHTML;
            } else {
                inventoryDataContainer.innerHTML = '<p>No se pudieron cargar los datos del inventario correctamente.</p>';
                console.error('Respuesta inesperada del backend:', data);
            }
        })
        .catch(error => {
            console.error('Error al cargar el inventario:', error);
            // Solo mostrar error si no fue por token inválido (ya que eso redirige)
            if (error.message !== 'Token Inválido/Expirado') {
                 inventoryDataContainer.innerHTML = `<p>Error al cargar datos: ${error.message}</p>`;
            }
             // En caso de error, restaurar la visibilidad de otras secciones si es necesario
            if(document.querySelector('.dashboard-widgets')) document.querySelector('.dashboard-widgets').style.display = 'grid';
            if(document.querySelector('.chart-placeholder')) document.querySelector('.chart-placeholder').style.display = 'block';
        });
    }
    // --- FIN DE LA NUEVA LÓGICA ---

    // --- Nueva Lógica para el Login Modal ---
    const loginForm = document.getElementById('loginForm');
    const loginModal = document.getElementById('loginModal');
    const loginErrorElement = document.getElementById('loginError');

    if (loginForm && loginModal) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault(); // Prevenir el envío normal del formulario

            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const loginButton = loginForm.querySelector('button[type="submit"]');
            loginButton.disabled = true;
            loginButton.textContent = 'Procesando...';
            if(loginErrorElement) loginErrorElement.style.display = 'none';


            fetch('backend/api/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: email, password: password })
            })
            .then(response => {
                if (!response.ok) {
                    // Si la respuesta no es OK (ej. 401, 400, 500),
                    // intentamos parsear el JSON para obtener el mensaje de error del backend.
                    return response.json().then(errData => {
                        throw new Error(errData.message || `Error ${response.status}: ${response.statusText}`);
                    });
                }
                return response.json(); // Si es OK, parseamos el JSON de la respuesta exitosa.
            })
            .then(data => {
                if (data.token) {
                    localStorage.setItem('jwtToken', data.token);
                    // Opcional: mostrar mensaje de éxito antes de redirigir
                    // loginModal.innerHTML = '<h2>¡Login Exitoso!</h2><p>Redirigiendo al dashboard...</p>';
                    window.location.href = 'dashboard.html';
                } else {
                    // Esto no debería ocurrir si el backend siempre devuelve un token en caso de éxito
                    // o un error claro en caso de fallo.
                    throw new Error(data.message || 'No se recibió el token.');
                }
            })
            .catch(error => {
                if (loginErrorElement) {
                    loginErrorElement.textContent = error.message || 'Error al intentar iniciar sesión.';
                    loginErrorElement.style.display = 'block';
                } else {
                    console.error('Error element not found:', error);
                    alert('Error al intentar iniciar sesión: ' + error.message);
                }
                loginButton.disabled = false;
                loginButton.textContent = 'Login';
            });
        });
    }

    // --- Fin de la Nueva Lógica para el Login Modal ---
});
