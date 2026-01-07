USE agro_management;
UPDATE users SET password_hash='$2y$10$r4SJ2Y.puMq29QoOOzRuX.wd9ef6gLVj.NuZsvAFzaf.inACHlhH2' WHERE email='admin@example.com';
SELECT id,name,email,password_hash FROM users WHERE email='admin@example.com';
