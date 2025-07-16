// Este script se ejecuta cuando el DOM está completamente cargado
document.addEventListener('DOMContentLoaded', function() {
    
    // Lógica para el formulario de agendar citas
    const especialidadSelect = document.getElementById('id_especialidad');
    const doctorSelect = document.getElementById('id_doctor');
    
    // Obtener la URL base desde un meta tag o un atributo de datos para mayor seguridad
    const baseUrl = document.querySelector('meta[name="base-url"]').getAttribute('content');

    if (especialidadSelect && doctorSelect) {
        especialidadSelect.addEventListener('change', function() {
            const especialidadId = this.value;
            doctorSelect.innerHTML = '<option value="">Cargando doctores...</option>';
            doctorSelect.disabled = true;

            // Si se deselecciona la especialidad, limpiar y deshabilitar el select de doctores
            if (!especialidadId) {
                doctorSelect.innerHTML = '<option value="">-- Primero selecciona una especialidad --</option>';
                return;
            }

            // Llamada a nuestra API para obtener los doctores de esa especialidad
            fetch(`${baseUrl}paciente/api_get_doctores.php?id_especialidad=${especialidadId}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Error en la respuesta de la red');
                    }
                    return response.json();
                })
                .then(data => {
                    doctorSelect.innerHTML = '<option value="">-- Selecciona un doctor --</option>';
                    if (data.length > 0) {
                        data.forEach(doctor => {
                            const option = document.createElement('option');
                            option.value = doctor.id_doctor;
                            option.textContent = doctor.nombre_completo;
                            doctorSelect.appendChild(option);
                        });
                        doctorSelect.disabled = false;
                    } else {
                        doctorSelect.innerHTML = '<option value="">-- No hay doctores para esta especialidad --</option>';
                    }
                })
                .catch(error => {
                    console.error('Error al cargar los doctores:', error);
                    doctorSelect.innerHTML = '<option value="">-- Error al cargar doctores --</option>';
                });
        });
    }

});