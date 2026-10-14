-- ===================================================
-- Semilla de Datos Iniciales: Échele Camello
-- Neiva, Huila
-- ===================================================

USE `echele_camello`;

-- Limpiar datos existentes respetando llaves foráneas
SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE `chat_messages`;
TRUNCATE TABLE `reviews`;
TRUNCATE TABLE `services`;
TRUNCATE TABLE `service_requests`;
TRUNCATE TABLE `workers`;
TRUNCATE TABLE `categories`;
TRUNCATE TABLE `audit_logs`;
SET FOREIGN_KEY_CHECKS = 1;

-- 1. Categorías
INSERT INTO `categories` (`id`, `name`, `icon_name`, `description`, `technician_count`) VALUES
('plomeria', 'Plomería & Tuberías', 'Wrench', 'Fugas, griferías, motobombas, sanitarios y destapes en Neiva.', 18),
('electricidad', 'Electricidad', 'Zap', 'Instalaciones certificadas, breakers, iluminación y cortos.', 24),
('aires', 'Aires Acondicionados', 'Wind', 'Mantenimiento preventivo, recarga de gas y montaje de minisplits.', 15),
('cerrajeria', 'Cerrajería', 'Key', 'Apertura de chapas, cambio de guardas, rejas y candados de seguridad.', 11),
('pintura', 'Pintura & Estuco', 'Paintbrush', 'Pintura de interiores y fachadas, resanes y acabados finos.', 14),
('construccion', 'Albañilería & Obra', 'Hammer', 'Enchapes de pisos, drywall, repellos y reformas generales.', 19),
('motos', 'Mecánica de Motos', 'Bike', 'Sincronización, frenos, kit de arrastre y auxilio a domicilio.', 12),
('carpinteria', 'Carpintería', 'Scissors', 'Cocinas integrales, clósets, puertas de madera y ajustes.', 9);

-- 2. Trabajadores
INSERT INTO `workers` (`id`, `name`, `email`, `phone`, `avatar`, `specialty`, `category_id`, `secondary_trades`, `bio`, `experience_years`, `sena_certified`, `certificate_title`, `neighborhood`, `distance_km`, `rating`, `total_reviews`, `completed_jobs`, `min_rate`, `is_available`, `working_hours`, `portfolio`) VALUES
(
  'w-1', 
  'Jairo Hernán Trujillo', 
  'jairo.trujillo@echelecamello.co', 
  '+57 312 458 9210', 
  'https://images.unsplash.com/photo-1540569014015-19a7be504e3a?auto=format&fit=crop&w=400&q=80', 
  'Electricista Certificado SENA', 
  'electricidad', 
  '["Iluminación LED", "Breakers & Tableros", "Acometidas"]', 
  'Más de 12 años iluminando hogares y locales en Neiva. Egresado del SENA Centro de la Industria, la Empresa y los Servicios (CIES). Cumplimiento estricto de norma RETIE.', 
  12, 
  1, 
  'Técnico en Instalaciones Eléctricas Residenciales (SENA CIES Neiva)', 
  'Cándido Leguízamo', 
  1.4, 
  4.90, 
  87, 
  142, 
  45000, 
  1, 
  'Lun - Sáb: 7:00 AM - 6:00 PM', 
  '["https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=600&q=80", "https://images.unsplash.com/photo-1558494949-ef010cbdcc31?auto=format&fit=crop&w=600&q=80"]'
),
(
  'w-2', 
  'Carlos Alberto Perdomo', 
  'carlos.perdomo@echelecamello.co', 
  '+57 318 732 1194', 
  'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 
  'Técnico de Climatización & Minisplits', 
  'aires', 
  '["Mantenimiento Preventivo", "Recarga R410A / R22", "Desmonte"]', 
  'En la calurosa Neiva nadie aguanta un aire dañado. Servicio express de limpieza hidrolavadora profunda, desinfección y recarga de refrigerante.', 
  8, 
  1, 
  'Certificación SENA en Mantenimiento de Sistemas de Refrigeración y Aire', 
  'Ipanema', 
  2.1, 
  4.80, 
  64, 
  98, 
  60000, 
  1, 
  'Lun - Dom: 6:30 AM - 7:00 PM', 
  '["https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80", "https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=600&q=80"]'
),
(
  'w-3', 
  'Fabio Nelson Charry', 
  'fabio.charry@echelecamello.co', 
  '+57 311 890 4421', 
  'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 
  'Maestro Plomero & Redes Hidráulicas', 
  'plomeria', 
  '["Destape con Sonda", "Tanques de Reserva", "Motobombas"]', 
  'Experto en solucionar filtraciones complejas y destapar tuberías sin romper baldosas innecesariamente. Equipado con sonda eléctrica y detector de fugas.', 
  15, 
  1, 
  'Técnico Laboral en Instalaciones Hidrosanitarias SENA', 
  'Las Granjas', 
  0.8, 
  4.95, 
  112, 
  215, 
  40000, 
  1, 
  'Atención diurna y emergencias fines de semana', 
  '["https://images.unsplash.com/photo-1584622650111-993a426fbf0a?auto=format&fit=crop&w=600&q=80", "https://images.unsplash.com/photo-1504307651254-35680f356dfd?auto=format&fit=crop&w=600&q=80"]'
),
(
  'w-4', 
  'Luz Dary Quintero', 
  'luzdary.quintero@echelecamello.co', 
  '+57 320 644 1903', 
  'https://images.unsplash.com/photo-1573496359142-b8d87734a5a2?auto=format&fit=crop&w=400&q=80', 
  'Pintura Decorativa & Acabados', 
  'pintura', 
  '["Estuco Veneciano", "Impermeabilización de Terrazas", "Drywall"]', 
  'Detallista, prolija y con gran sentido del color. Transformamos casas y apartamentos con pinturas lavables de alta calidad y protección contra la humedad del Huila.', 
  9, 
  1, 
  'Certificada SENA en Aplicación de Recubrimientos y Acabados Arquitectónicos', 
  'Prado Alto', 
  3.2, 
  4.90, 
  45, 
  73, 
  50000, 
  0, 
  'Lun - Sáb: 7:30 AM - 5:30 PM', 
  '["https://images.unsplash.com/photo-1562259949-e8e7689d7828?auto=format&fit=crop&w=600&q=80", "https://images.unsplash.com/photo-1589939705384-5185137a7f0f?auto=format&fit=crop&w=600&q=80"]'
),
(
  'w-5', 
  'Andrés Felipe Calderón', 
  'andres.cerrajero@echelecamello.co', 
  '+57 315 901 8832', 
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 
  'Cerrajería de Emergencia 24/7', 
  'cerrajeria', 
  '["Chapas de Seguridad", "Candados blindados", "Apertura de Carros"]', 
  'Respuesta rápida en moto a cualquier barrio de Neiva. Si olvidó las llaves adentro o se le trabó la chapa, abro sin dañar la puerta.', 
  7, 
  1, 
  'Técnico Metalmecánico & Cerrajería de Seguridad SENA', 
  'Centro', 
  0.5, 
  4.85, 
  93, 
  180, 
  35000, 
  1, 
  'Servicio 24 horas todos los días', 
  '["https://images.unsplash.com/photo-1558002038-1055907df827?auto=format&fit=crop&w=600&q=80"]'
),
(
  'w-6', 
  'Brayan Alexis Méndez', 
  'brayan.motos@echelecamello.co', 
  '+57 317 220 9015', 
  'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=400&q=80', 
  'Mecánico de Motocicletas a Domicilio', 
  'motos', 
  '["Pinchazos & Llantas", "Cambio de Aceite & Pastillas", "Sistema Eléctrico"]', 
  'Neiva se mueve en moto. Atiendo varadas a domicilio o rescate en carretera: calibración de válvulas, bujías, kit de arrastre y frenos.', 
  6, 
  1, 
  'Técnico en Mantenimiento de Motores de Combustión Interna - SENA Huila', 
  'Canaima', 
  3.8, 
  4.78, 
  52, 
  89, 
  30000, 
  1, 
  'Lun - Sáb: 7:00 AM - 7:00 PM', 
  '["https://images.unsplash.com/photo-1558981403-c5f9899a28bc?auto=format&fit=crop&w=600&q=80"]'
);

-- 3. Servicios
INSERT INTO `services` (`id`, `worker_id`, `category_id`, `title`, `description`, `estimated_price`, `price_type`, `is_active`) VALUES
('s-1-1', 'w-1', 'electricidad', 'Revisión y Diagnóstico de Cortocircuito', 'Detección de falla con multímetro, aislamiento de línea y prueba de carga.', 45000, 'desde', 1),
('s-1-2', 'w-1', 'electricidad', 'Instalación de Ventilador de Techo o Lámpara', 'Fijación segura a losa o cielo raso, cableado y conexión con interruptor.', 55000, 'fijo', 1),
('s-1-3', 'w-1', 'electricidad', 'Cambio o Actualización de Tablero de Breakers', 'Reemplazo de caja de distribución, peinado de cables y balanceo de fases.', 120000, 'desde', 1),

('s-2-1', 'w-2', 'aires', 'Mantenimiento Preventivo Profundo Minisplit', 'Lavado a presión de turbina, evaporador, condensador, bandeja y aplicación de antibacteriano.', 70000, 'fijo', 1),
('s-2-2', 'w-2', 'aires', 'Carga de Gas Refrigerante (R410A / R22)', 'Chequeo de presiones con manómetro, vacío de línea y carga por báscula o pesaje.', 90000, 'desde', 1),
('s-2-3', 'w-2', 'aires', 'Instalación Básica Minisplit 12,000 BTU', 'Fijación de consolas interior y exterior, perforación de pared e interconexión.', 150000, 'desde', 1),

('s-3-1', 'w-3', 'plomeria', 'Destape de Sifón o Lavaplatos con Sonda', 'Sondeo mecánico sin maltratar tubos de PVC, prueba de caudal y desinfección.', 50000, 'fijo', 1),
('s-3-2', 'w-3', 'plomeria', 'Reparación de Fuga en Tubería PVC o Grifería', 'Corte de sección con fuga, empate con uniones rápidas o soldadura PVC.', 40000, 'desde', 1),
('s-3-3', 'w-3', 'plomeria', 'Lavado y Desinfección de Tanque de Reserva', 'Vaciado, cepillado con hipoclorito, purga y calibración de flotador.', 65000, 'fijo', 1),

('s-4-1', 'w-4', 'pintura', 'Pintura de Habitación Completa (Muros y Cielo)', 'Preparación de pared, resane de grietas menores y 2 manos de vinilo tipo 1.', 95000, 'desde', 1),
('s-4-2', 'w-4', 'pintura', 'Impermeabilización de Techo o Cubierta', 'Sellado de goteras, aplicación de tela asfáltica o impermeabilizante elastomérico.', 180000, 'desde', 1),

('s-5-1', 'w-5', 'cerrajeria', 'Apertura de Puerta Residencial Trabada / Llaves adentro', 'Uso de ganzúas de precisión sin fracturar el cilindro.', 40000, 'fijo', 1),
('s-5-2', 'w-5', 'cerrajeria', 'Instalación o Cambio de Cerrojo de Seguridad', 'Montaje de cerradura de 3 o 4 pasadores para mayor seguridad en puertas principales.', 65000, 'desde', 1),

('s-6-1', 'w-6', 'motos', 'Mantenimiento Preventivo a Domicilio (Frenos + Aceite + Cadena)', 'Lubricación, tensión y limpieza de cadena, cambio de aceite y graduación de frenos.', 35000, 'fijo', 1),
('s-6-2', 'w-6', 'motos', 'Sincronización y Limpieza de Carburador / Inyección', 'Calibrado fino de ralentí, limpieza con líquido dieléctrico y cambio de filtro de aire.', 55000, 'desde', 1);

-- 4. Reseñas
INSERT INTO `reviews` (`id`, `worker_id`, `request_id`, `client_name`, `client_avatar`, `rating`, `comment`, `review_date`, `service_title`) VALUES
('r-1', 'w-1', NULL, 'María Fernanda Ospina', 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=150&q=80', 5, 'Excelente maestro. Llegó puntual a Ipanema, me arregló un corto en la cocina que llevaba semanas y dejó todo impecable.', 'Hace 3 días', 'Revisión y Diagnóstico de Cortocircuito'),
('r-2', 'w-1', NULL, 'Carlos Mario Dussán', 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=150&q=80', 5, 'Muy conocedor de la norma técnica. Don Jairo es un señor serio y cobra lo justo sin rodeos.', 'Hace 1 semana', 'Instalación de Ventilador de Techo'),
('r-3', 'w-2', NULL, 'Adriana Lucía Vargas', 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=150&q=80', 5, 'El aire del cuarto estaba goteando y enfriando poco. Carlos vino en menos de 2 horas y lo dejó como nuevo.', 'Hace 2 días', 'Mantenimiento Preventivo Profundo Minisplit'),
('r-4', 'w-3', NULL, 'Guillermo Borrero', 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?auto=format&fit=crop&w=150&q=80', 5, 'Don Fabio es el mejor plomero de Neiva. Me salvó de una inundación en el baño principal un sábado en la tarde. 100% recomendado.', 'Ayer', 'Reparación de Fuga en Tubería PVC'),
('r-5', 'w-4', NULL, 'Sonia Rodríguez', 'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=150&q=80', 5, 'Luz Dary es increíble. Dejó el apartamento hermoso, cuidó los muebles tapándolos con plástico y terminó antes de tiempo.', 'Hace 5 días', 'Pintura de Habitación Completa'),
('r-6', 'w-5', NULL, 'Jorge Eduardo Tovar', 'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61?auto=format&fit=crop&w=150&q=80', 5, 'Me quedé por fuera a las 10 de la noche y Andrés llegó en 15 minutos al Quirinal. Excelente servicio y muy formal.', 'Hace 4 días', 'Apertura de Puerta Residencial'),
('r-7', 'w-6', NULL, 'Néstor Fabio Cabrera', 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=150&q=80', 5, 'Me desvaró la moto frente a la gobernación. Súper rápido y con todas las herramientas en la maleta.', 'Hace 6 días', 'Mantenimiento Preventivo a Domicilio');

-- 5. Solicitudes de Servicio
INSERT INTO `service_requests` (`id`, `code`, `client_id`, `client_name`, `client_phone`, `client_avatar`, `worker_id`, `worker_name`, `worker_specialty`, `worker_avatar`, `service_title`, `category`, `status`, `scheduled_date`, `scheduled_time`, `address`, `neighborhood`, `description`, `estimated_cost`, `urgent`, `diagnosis_photo`, `has_review`, `created_at`, `updated_at`) VALUES
(
  'req-101', 
  'CAM-2026-081', 
  'cli-1', 
  'Valentina Medina', 
  '+57 311 234 5678', 
  'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80', 
  'w-1', 
  'Jairo Hernán Trujillo', 
  'Electricista Certificado SENA', 
  'https://images.unsplash.com/photo-1540569014015-19a7be504e3a?auto=format&fit=crop&w=400&q=80', 
  'Revisión y Diagnóstico de Cortocircuito', 
  'Electricidad', 
  'EN_PROCESO', 
  '2026-10-07', 
  '15:30', 
  'Calle 26 # 1W - 45, Casa 3', 
  'Cándido Leguízamo', 
  'Se saltó el breaker principal y un enchufe del patio huele a quemado tras el aguacero de ayer.', 
  45000, 
  1, 
  'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=600&q=80', 
  0, 
  '2026-10-07 10:15', 
  '2026-10-07 13:30'
),
(
  'req-102', 
  'CAM-2026-082', 
  'cli-2', 
  'Esteban Cuenca', 
  '+57 320 889 1234', 
  'https://images.unsplash.com/photo-1535713875002-d1d0cf377fde?auto=format&fit=crop&w=150&q=80', 
  'w-2', 
  'Carlos Alberto Perdomo', 
  'Técnico de Climatización & Minisplits', 
  'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=400&q=80', 
  'Mantenimiento Preventivo Profundo Minisplit', 
  'Aires Acondicionados', 
  'ACEPTADA', 
  '2026-10-08', 
  '09:00', 
  'Carrera 42 # 8 - 19, Torre B Apto 502', 
  'Ipanema', 
  'Minisplit Samsung de 18000 BTU no enfría suficiente y bota agua por la carcasa interior.', 
  70000, 
  0, 
  NULL, 
  0, 
  '2026-10-06 18:20', 
  '2026-10-07 08:00'
),
(
  'req-103', 
  'CAM-2026-083', 
  'cli-3', 
  'Diana Marcela Peña', 
  '+57 314 990 4123', 
  'https://images.unsplash.com/photo-1580489944761-15a19d654956?auto=format&fit=crop&w=150&q=80', 
  'w-3', 
  'Fabio Nelson Charry', 
  'Maestro Plomero & Redes Hidráulicas', 
  'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?auto=format&fit=crop&w=400&q=80', 
  'Destape de Sifón o Lavaplatos con Sonda', 
  'Plomería & Tuberías', 
  'PENDIENTE', 
  '2026-10-08', 
  '11:00', 
  'Calle 18 # 14 - 32', 
  'Las Granjas', 
  'El sifón de la lavadora está devolviendo espuma y agua cuando desagua. Se requiere sonda.', 
  50000, 
  1, 
  NULL, 
  0, 
  '2026-10-07 12:45', 
  '2026-10-07 12:45'
),
(
  'req-104', 
  'CAM-2026-079', 
  'cli-1', 
  'Valentina Medina', 
  '+57 311 234 5678', 
  'https://images.unsplash.com/photo-1494790108377-be9c29b29330?auto=format&fit=crop&w=150&q=80', 
  'w-5', 
  'Andrés Felipe Calderón', 
  'Cerrajería de Emergencia 24/7', 
  'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?auto=format&fit=crop&w=400&q=80', 
  'Apertura de Puerta Residencial Trabada', 
  'Cerrajería', 
  'FINALIZADA', 
  '2026-10-05', 
  '19:00', 
  'Calle 26 # 1W - 45', 
  'Cándido Leguízamo', 
  'Se rompió la llave dentro del cerrojo de la puerta principal.', 
  40000, 
  0, 
  NULL, 
  1, 
  '2026-10-05 18:30', 
  '2026-10-05 19:40'
);

-- 6. Mensajes de Chat
INSERT INTO `chat_messages` (`id`, `request_id`, `sender_id`, `sender_name`, `sender_role`, `text`, `timestamp`) VALUES
('msg-1', 'req-101', 'cli-1', 'Valentina Medina', 'CLIENTE', 'Buenas tardes maestro Jairo, le envié la solicitud porque el breaker principal se bajó solo y el enchufe del patio huele a recalentado.', '10:18'),
('msg-2', 'req-101', 'w-1', 'Jairo Hernán Trujillo', 'TRABAJADOR', 'Buenas tardes Valentina. Por seguridad, por favor no suba el breaker hasta que yo revise. Llego en camino sobre las 3:30 PM con multímetro y repuestos.', '10:25'),
('msg-3', 'req-101', 'cli-1', 'Valentina Medina', 'CLIENTE', 'Perfecto maestro, aquí lo espero en Cándido, Casa 3 reja blanca.', '10:28'),
('msg-4', 'req-101', 'w-1', 'Jairo Hernán Trujillo', 'TRABAJADOR', 'Ya me encuentro en el sector, inicio camello en 5 minutos.', '13:30');

-- 7. Registros de Auditoría
INSERT INTO `audit_logs` (`id`, `action`, `user`, `timestamp`, `schema_name`, `details`, `severity`) VALUES
('aud-1', 'SCHEMA_INIT', 'system_init', '2026-10-07 08:00:00', 'schema_usuarios', 'Tablas de base de datos creadas con éxito vía MySQL XAMPP (puerto 3306).', 'INFO'),
('aud-2', 'TECH_VERIFIED', 'admin_adso_sena', '2026-10-07 09:12:15', 'schema_usuarios', 'Validado certificado SENA CIES Neiva para Jairo Hernán Trujillo (Técnico RETIE).', 'INFO'),
('aud-3', 'SERVICE_REQUEST_CREATED', 'cli_valentina_medina', '2026-10-07 10:15:33', 'schema_solicitudes', 'Nueva solicitud CAM-2026-081 emitida para w-1 (Electricidad / Cándido Leguízamo).', 'INFO'),
('aud-4', 'STATUS_TRANSITION', 'w_jairo_trujillo', '2026-10-07 13:30:00', 'schema_solicitudes', 'Solicitud CAM-2026-081 cambió a estado EN_PROCESO.', 'INFO');
