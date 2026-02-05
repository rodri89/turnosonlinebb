<script>
    window.OneSignal = window.OneSignal || [];  
    OneSignal.push(function() {
        OneSignal.init({
            appId: "87602532-8d15-4f44-9888-faec4e96673a",
            autoRegister: true,
            notifyButton: { enable: false },
            promptOptions: {
                slidedown: {
                    enabled: true,
                    autoPrompt: true,
                    timeDelay: 2,
                    pageViews: 1,
                    message: "¡Activa notificaciones para recibir recordatorios semanales!",
                    acceptButtonText: "ACTIVAR",
                    cancelButtonText: "NO, GRACIAS"
                }
            }
        });

        OneSignal.getUserId().then(function(userId) {
            if (userId) {
                console.log("OneSignal User ID:", userId);
                fetch('/guardar-user-id', {
                    method: 'POST',
                    headers: { 
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        user_id: userId,
                        paciente_id: "{{ $paciente->id ?? '' }}"
                    })
                });
            }
        });
    });
</script>