<div>
    <style>
        /* Namespace único para el modal de telemedicina */
        .telemedicina-modal .modal-content {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: none;
        }
        
        .telemedicina-modal .modal-header {
            background: linear-gradient(to right, #2c3e50, #4a6491);
            color: white;
            padding: 25px 30px;
            border-bottom: none;
        }
        
        .telemedicina-modal .modal-title {
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 5px;
            letter-spacing: 0.5px;
        }
        
        .telemedicina-modal .modal-subtitle {
            font-size: 18px;
            opacity: 0.9;
            font-weight: 400;
        }
        
        .telemedicina-modal .modal-body {
            padding: 30px;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }
        
        /* Clases con namespace específico */
        .telemedicina-section {
            margin-bottom: 25px;
        }
        
        .telemedicina-section-title {
            font-size: 20px;
            color: #2c3e50;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 2px solid #eaeaea;
            font-weight: 600;
            position: relative;
        }
        
        .telemedicina-section-title::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 50px;
            height: 2px;
            background-color: #4a6491;
        }
        
        .telemedicina-list {
            list-style-type: none;
            padding-left: 0;
            margin-bottom: 0;
        }
        
        .telemedicina-list-item {
            padding: 10px 0;
            position: relative;
            padding-left: 25px;
            color: #555;
            line-height: 1.5;
            display: flex;
            align-items: flex-start;
        }
        
        .telemedicina-list-item::before {
            content: '•';
            color: #4a6491;
            font-weight: bold;
            font-size: 20px;
            position: absolute;
            left: 0;
            top: 8px;
        }
        
        .telemedicina-icon {
            margin-right: 10px;
            color: #4a6491;
            font-size: 18px;
        }
        
        .telemedicina-modal .modal-footer {
            background-color: #f8f9fa;
            padding: 20px 30px;
            border-top: 1px solid #eaeaea;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 15px;
        }
        
        .telemedicina-whatsapp-text {
            font-size: 16px;
            color: #2c3e50;
            text-align: center;
            margin: 0;
        }
        
        .telemedicina-btn-whatsapp {
            background-color: #25D366;
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 8px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            text-decoration: none;
        }
        
        .telemedicina-btn-whatsapp:hover {
            background-color: #128C7E;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 211, 102, 0.3);
        }
        
        .telemedicina-whatsapp-icon {
            font-size: 20px;
        }
        
        .telemedicina-btn-close {
            background: rgba(255, 255, 255, 0.2);
            border: none;
            color: white;
            width: 36px;
            height: 36px;
            border-radius: 50%;
            font-size: 18px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            opacity: 1;
        }
        
        .telemedicina-btn-close:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: rotate(90deg);
            opacity: 1;
        }
        
        @media (max-width: 768px) {
            .telemedicina-modal .modal-body {
                grid-template-columns: 1fr;
                padding: 20px;
            }
            
            .telemedicina-modal .modal-header {
                padding: 20px;
            }
            
            .telemedicina-modal .modal-title {
                font-size: 24px;
            }
        }
    </style>

    <div class="modal fade telemedicina-modal" id="modalTelemedicina" tabindex="-1" aria-labelledby="modalTelemedicinaLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h1 class="modal-title" id="modalTelemedicinaLabel">Telemedicina</h1>
                        <p class="modal-subtitle">Atención Médica de Calidad, Donde Estés</p>
                    </div>
                    <button type="button" class="telemedicina-btn-close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">×</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="telemedicina-section">
                        <h3 class="telemedicina-section-title">Servicios que Ofrecemos</h3>
                        <ul class="telemedicina-list">
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">📋</span>
                                <span>Recetas médicas</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">🧪</span>
                                <span>Análisis de laboratorio</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">📄</span>
                                <span>Certificados médicos</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">💬</span>
                                <span>Otras consultas</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="telemedicina-section">
                        <h3 class="telemedicina-section-title">Seguimiento de Patologías Crónicas</h3>
                        <ul class="telemedicina-list">
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">🩺</span>
                                <span>Diabetes</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">🌬️</span>
                                <span>Asma</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">❤️</span>
                                <span>Hipertensión</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">⚖️</span>
                                <span>Obesidad</span>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="telemedicina-section">
                        <h3 class="telemedicina-section-title">Información Útil</h3>
                        <ul class="telemedicina-list">
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">🔍</span>
                                <span>Acceso fácil a la atención</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">📊</span>
                                <span>Monitoreo continuo</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">⚙️</span>
                                <span>Ajustes de tratamiento</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">💬</span>
                                <span>Comunicación más efectiva</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">⏱️</span>
                                <span>Reducción de tiempos</span>
                            </li>
                            <li class="telemedicina-list-item">
                                <span class="telemedicina-icon">😌</span>
                                <span>Reducción de estrés y ansiedad</span>
                            </li>
                        </ul>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <p class="telemedicina-whatsapp-text">¿Desea obtener un turno o tiene alguna consulta?</p>
                    <p class="telemedicina-whatsapp-text">Haga clic en el siguiente botón para contactarnos por WhatsApp:</p>
                    <a href="https://wa.me/5492915107335?text=Hola,%20me%20interesa%20solicitar%20un%20turno%20de%20telemedicina" 
                       class="telemedicina-btn-whatsapp" 
                       target="_blank">
                        <span class="telemedicina-whatsapp-icon">📱</span>
                        Contactar por WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    
</div>