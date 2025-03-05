-- Sample data (optional, for testing)
INSERT INTO resource_utilization (resource_name, usage_percentage, recorded_date) VALUES
('Water', 85, '2025-03-05'),
('Electricity', 92, '2025-03-05'),
('Internet', 78, '2025-03-05'),
('Gas', 65, '2025-03-05');

INSERT INTO users (fullname, email, contact_number, password, role) VALUES
('Muthu Kumar', 'muthu.kumar@example.com', '9876543210', 'hashed_password1', 'admin'),
('Priya Selvam', 'priya.selvam@example.com', '8765432109', 'hashed_password2', 'user'),
('Ravi Shankar', 'ravi.shankar@example.com', '7654321098', 'hashed_password3', 'user'),
('Lakshmi Devi', 'lakshmi.devi@example.com', '6543210987', 'hashed_password4', 'user');

INSERT INTO population (total, adults, seniors, youth, children, recorded_date) VALUES
(5000, 3000, 800, 700, 500, '2025-03-01'),
(5050, 3050, 810, 690, 500, '2025-02-01'),
(4980, 2980, 790, 710, 500, '2025-01-01');

INSERT INTO projects (name, description, status, start_date, end_date) VALUES
('Water Tank Construction', 'Building a new water tank for the village', 'active', '2025-01-15', '2025-06-30'),
('Road Repair', 'Repairing the main road to Gudalur market', 'completed', '2024-11-01', '2025-02-28'),
('Solar Panel Installation', 'Installing solar panels for street lighting', 'pending', NULL, NULL);

INSERT INTO emergency_alerts (title, description, status) VALUES
('Heavy Rainfall Warning', 'Expected heavy rain in Gudalur, risk of flooding', 'active'),
('Landslide Alert', 'Landslide reported near tea estate', 'resolved'),
('Power Outage Notice', 'Scheduled maintenance, power cut expected', 'active');

INSERT INTO bus_schedules (route, next_arrival, status, capacity_percentage, schedule_date) VALUES
('Gudalur to Ooty', '08:30:00', 'on_time', 70, '2025-03-06'),
('Gudalur to Coimbatore', '10:15:00', 'delayed', 90, '2025-03-06'),
('Gudalur to Nilgiri', '14:00:00', 'cancelled', 0, '2025-03-06');

INSERT INTO activities (title, description, activity_type) VALUES
('Water Tank Planning', 'Meeting to discuss water tank project', 'meeting'),
('Road Repair Completion', 'Celebration for road repair completion', 'project'),
('Flood Preparedness', 'Community briefing on heavy rain', 'alert');

INSERT INTO weather_forecast (day, temperature, weather_condition, forecast_date) VALUES
('Wednesday', 22, 'Partly Cloudy', '2025-03-05'),
('Thursday', 20, 'Rain', '2025-03-06'),
('Friday', 23, 'Sunny', '2025-03-07');

INSERT INTO contact_messages (name, email, message) VALUES
('Suresh Raj', 'suresh.raj@example.com', 'Need update on water tank project'),
('Meena Kumari', 'meena.kumari@example.com', 'Bus to Ooty was late today'),
('Karthik G', 'karthik.g@example.com', 'Request more street lights near school');


INSERT INTO resource_utilization (resource_name, usage_percentage, recorded_date) VALUES
('Water Supply', 85, '2025-03-01'),
('Electricity', 70, '2025-03-01'),
('Internet Bandwidth', 60, '2025-03-01');

INSERT INTO complaints (name, email, phone_number, complaint, file_path) VALUES
('Anitha R', 'anitha.r@example.com', '9123456789', 'Potholes on market road', '/uploads/pothole.jpg'),
('Vijay M', 'vijay.m@example.com', '8234567890', 'No water supply since morning', NULL),
('Saranya K', 'saranya.k@example.com', '7345678901', 'Bus cancelled without notice', '/uploads/ticket.pdf');