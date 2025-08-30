<?php
/**
 * Email Configuration and Functions for LimpaBrasil
 * Compatible with cPanel email system
 */

// Email configuration - Update for your cPanel setup
define('SMTP_HOST', 'localhost'); // Usually localhost for cPanel
define('SMTP_PORT', 587); // or 465 for SSL
define('SMTP_USERNAME', 'contato@yourdomain.com');
define('SMTP_PASSWORD', 'your_email_password');
define('SMTP_ENCRYPTION', 'tls'); // tls or ssl

// Company email settings
define('COMPANY_EMAIL', 'contato@limpabrasil.com.br');
define('COMPANY_NAME', 'LimpaBrasil');
define('ADMIN_EMAIL', 'admin@limpabrasil.com.br');

/**
 * Send email using PHP mail() function (cPanel compatible)
 * @param string $to Recipient email
 * @param string $subject Email subject
 * @param string $message Email body (HTML)
 * @param array $headers Additional headers
 * @return bool True if sent successfully
 */
function sendEmail($to, $subject, $message, $headers = []) {
    // Default headers
    $defaultHeaders = [
        'MIME-Version' => '1.0',
        'Content-type' => 'text/html; charset=UTF-8',
        'From' => COMPANY_NAME . ' <' . COMPANY_EMAIL . '>',
        'Reply-To' => COMPANY_EMAIL,
        'X-Mailer' => 'PHP/' . phpversion()
    ];
    
    // Merge headers
    $allHeaders = array_merge($defaultHeaders, $headers);
    
    // Convert headers array to string
    $headerString = '';
    foreach ($allHeaders as $key => $value) {
        $headerString .= $key . ': ' . $value . "\r\n";
    }
    
    // Send email
    try {
        $result = mail($to, $subject, $message, $headerString);
        
        if ($result) {
            error_log("Email sent successfully to: $to");
            return true;
        } else {
            error_log("Failed to send email to: $to");
            return false;
        }
    } catch (Exception $e) {
        error_log("Email error: " . $e->getMessage());
        return false;
    }
}

/**
 * Send contact form email
 */
function sendContactEmail($nome, $email, $telefone, $assunto, $endereco, $mensagem) {
    $subject = "[LimpaBrasil] Nova mensagem de contato - $assunto";
    
    $message = getEmailTemplate('contact', [
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'assunto' => $assunto,
        'endereco' => $endereco,
        'mensagem' => $mensagem,
        'data' => date('d/m/Y H:i:s')
    ]);
    
    // Send to admin
    $adminSent = sendEmail(ADMIN_EMAIL, $subject, $message);
    
    // Send confirmation to customer
    $confirmationSubject = "[LimpaBrasil] Recebemos sua mensagem";
    $confirmationMessage = getEmailTemplate('contact_confirmation', [
        'nome' => $nome,
        'assunto' => $assunto
    ]);
    
    $customerSent = sendEmail($email, $confirmationSubject, $confirmationMessage);
    
    return $adminSent && $customerSent;
}

/**
 * Send booking confirmation email
 */
function sendBookingEmail($nome, $email, $telefone, $tipo_servico, $frequencia, $data_preferida, $horario_preferido, $endereco, $observacoes, $agendamento_id) {
    $subject = "[LimpaBrasil] Novo agendamento #$agendamento_id";
    
    $message = getEmailTemplate('booking', [
        'nome' => $nome,
        'email' => $email,
        'telefone' => $telefone,
        'tipo_servico' => ucfirst(str_replace('-', ' ', $tipo_servico)),
        'frequencia' => ucfirst($frequencia),
        'data_preferida' => date('d/m/Y', strtotime($data_preferida)),
        'horario_preferido' => ucfirst(str_replace('_', ' ', $horario_preferido)),
        'endereco' => $endereco,
        'observacoes' => $observacoes,
        'agendamento_id' => $agendamento_id,
        'data' => date('d/m/Y H:i:s')
    ]);
    
    // Send to admin
    $adminSent = sendEmail(ADMIN_EMAIL, $subject, $message);
    
    // Send confirmation to customer
    $confirmationSubject = "[LimpaBrasil] Agendamento confirmado #$agendamento_id";
    $confirmationMessage = getEmailTemplate('booking_confirmation', [
        'nome' => $nome,
        'agendamento_id' => $agendamento_id,
        'tipo_servico' => ucfirst(str_replace('-', ' ', $tipo_servico)),
        'data_preferida' => date('d/m/Y', strtotime($data_preferida)),
        'horario_preferido' => ucfirst(str_replace('_', ' ', $horario_preferido))
    ]);
    
    $customerSent = sendEmail($email, $confirmationSubject, $confirmationMessage);
    
    return $adminSent && $customerSent;
}

/**
 * Get email template
 */
function getEmailTemplate($template, $data = []) {
    $templates = [
        'contact' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: #0284c7; color: white; padding: 20px; text-align: center;">
                    <h1>Nova Mensagem de Contato</h1>
                </div>
                <div style="padding: 20px; background: #f8fafc;">
                    <h2>Detalhes da Mensagem</h2>
                    <table style="width: 100%; border-collapse: collapse;">
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Nome:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{nome}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>E-mail:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{email}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Telefone:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{telefone}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Assunto:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{assunto}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Endereço:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{endereco}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Data:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{data}</td></tr>
                    </table>
                    <h3>Mensagem:</h3>
                    <div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #0284c7;">
                        {mensagem}
                    </div>
                </div>
                <div style="background: #334155; color: white; padding: 15px; text-align: center;">
                    <p>LimpaBrasil - Sistema de Gestão</p>
                </div>
            </div>
        ',
        
        'contact_confirmation' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: #0284c7; color: white; padding: 20px; text-align: center;">
                    <h1>Mensagem Recebida</h1>
                </div>
                <div style="padding: 20px; background: #f8fafc;">
                    <p>Olá <strong>{nome}</strong>,</p>
                    <p>Recebemos sua mensagem sobre "<strong>{assunto}</strong>" e entraremos em contato em breve.</p>
                    <p>Nossa equipe responde em até 2 horas úteis.</p>
                    <p>Obrigado por entrar em contato com a LimpaBrasil!</p>
                </div>
                <div style="background: #334155; color: white; padding: 15px; text-align: center;">
                    <p>LimpaBrasil - Sua empresa de limpeza profissional</p>
                    <p>Tel: (11) 99999-9999 | E-mail: contato@limpabrasil.com.br</p>
                </div>
            </div>
        ',
        
        'booking' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: #0284c7; color: white; padding: 20px; text-align: center;">
                    <h1>Novo Agendamento #{agendamento_id}</h1>
                </div>
                <div style="padding: 20px; background: #f8fafc;">
                    <h2>Dados do Cliente</h2>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Nome:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{nome}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>E-mail:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{email}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Telefone:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{telefone}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Endereço:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{endereco}</td></tr>
                    </table>
                    
                    <h2>Detalhes do Serviço</h2>
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px;">
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Tipo de Serviço:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{tipo_servico}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Frequência:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{frequencia}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Data Preferida:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{data_preferida}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Horário:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{horario_preferido}</td></tr>
                        <tr><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;"><strong>Solicitado em:</strong></td><td style="padding: 8px; border-bottom: 1px solid #e2e8f0;">{data}</td></tr>
                    </table>
                    
                    {observacoes ? <div><h3>Observações:</h3><div style="background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #0284c7;">{observacoes}</div></div> : ""}
                </div>
                <div style="background: #334155; color: white; padding: 15px; text-align: center;">
                    <p>LimpaBrasil - Sistema de Gestão</p>
                </div>
            </div>
        ',
        
        'booking_confirmation' => '
            <div style="font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;">
                <div style="background: #0284c7; color: white; padding: 20px; text-align: center;">
                    <h1>Agendamento Confirmado</h1>
                </div>
                <div style="padding: 20px; background: #f8fafc;">
                    <p>Olá <strong>{nome}</strong>,</p>
                    <p>Seu agendamento foi recebido com sucesso!</p>
                    
                    <div style="background: white; padding: 15px; border-radius: 5px; border: 1px solid #e2e8f0; margin: 20px 0;">
                        <h3 style="margin-top: 0;">Detalhes do Agendamento #{agendamento_id}</h3>
                        <p><strong>Serviço:</strong> {tipo_servico}</p>
                        <p><strong>Data:</strong> {data_preferida}</p>
                        <p><strong>Horário:</strong> {horario_preferido}</p>
                    </div>
                    
                    <p>Nossa equipe entrará em contato em até 2 horas úteis para confirmação dos detalhes e agendamento.</p>
                    <p>Qualquer dúvida, entre em contato conosco:</p>
                    <ul>
                        <li>Telefone: (11) 99999-9999</li>
                        <li>WhatsApp: (11) 99999-9999</li>
                        <li>E-mail: contato@limpabrasil.com.br</li>
                    </ul>
                    <p>Obrigado por escolher a LimpaBrasil!</p>
                </div>
                <div style="background: #334155; color: white; padding: 15px; text-align: center;">
                    <p>LimpaBrasil - Sua empresa de limpeza profissional</p>
                    <p>Tel: (11) 99999-9999 | E-mail: contato@limpabrasil.com.br</p>
                </div>
            </div>
        '
    ];
    
    $template = $templates[$template] ?? '';
    
    // Replace placeholders
    foreach ($data as $key => $value) {
        $template = str_replace('{' . $key . '}', htmlspecialchars($value), $template);
    }
    
    return $template;
}

/**
 * Send status update email to customer
 */
function sendStatusUpdateEmail($email, $nome, $agendamento_id, $novo_status, $data_servico = null) {
    $subject = "[LimpaBrasil] Atualização do agendamento #$agendamento_id";
    
    $status_messages = [
        'confirmado' => 'Seu agendamento foi confirmado! Nossa equipe chegará no horário combinado.',
        'concluido' => 'Serviço concluído com sucesso! Obrigado por escolher a LimpaBrasil.',
        'cancelado' => 'Seu agendamento foi cancelado. Entre em contato para reagendar.'
    ];
    
    $status_message = $status_messages[$novo_status] ?? 'Status do agendamento atualizado.';
    
    $message = "
        <div style=\"font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;\">
            <div style=\"background: #0284c7; color: white; padding: 20px; text-align: center;\">
                <h1>Atualização de Agendamento</h1>
            </div>
            <div style=\"padding: 20px; background: #f8fafc;\">
                <p>Olá <strong>$nome</strong>,</p>
                <p>Temos uma atualização sobre seu agendamento #$agendamento_id:</p>
                <div style=\"background: white; padding: 15px; border-radius: 5px; border-left: 4px solid #0284c7; margin: 20px 0;\">
                    <p><strong>Status:</strong> " . ucfirst($novo_status) . "</p>
                    <p>$status_message</p>
                </div>
                <p>Qualquer dúvida, entre em contato conosco!</p>
            </div>
            <div style=\"background: #334155; color: white; padding: 15px; text-align: center;\">
                <p>LimpaBrasil - Sua empresa de limpeza profissional</p>
                <p>Tel: (11) 99999-9999 | E-mail: contato@limpabrasil.com.br</p>
            </div>
        </div>
    ";
    
    return sendEmail($email, $subject, $message);
}

/**
 * Test email configuration
 */
function testEmailConfiguration() {
    $testEmail = ADMIN_EMAIL;
    $subject = "Teste de configuração de e-mail - LimpaBrasil";
    $message = "
        <h2>Teste de E-mail</h2>
        <p>Se você recebeu este e-mail, a configuração está funcionando corretamente.</p>
        <p>Data: " . date('d/m/Y H:i:s') . "</p>
    ";
    
    return sendEmail($testEmail, $subject, $message);
}
?>
