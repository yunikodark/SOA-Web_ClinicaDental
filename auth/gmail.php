 /* ───── Configuración SMTP ───── */
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'minimarketllauce14@gmail.com';    // tu cuenta
    $mail->Password   = 'cqgtkvzwumbbqoki';                // pass‑app
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;



    