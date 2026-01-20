<?php
namespace OpenWHM\Core;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

/**
 * Email Handler
 */
class Mailer
{
    private $db;
    private $hooks;
    
    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->hooks = Application::getInstance()->getHooks();
    }
    
    /**
     * Send email using template
     */
    public function sendTemplate($templateName, $to, $data = [])
    {
        // Get template from database
        $template = $this->db->fetch(
            "SELECT * FROM {$this->db->table('email_templates')} WHERE name = ? AND disabled = 0",
            [$templateName]
        );
        
        if (!$template) {
            Logger::warning("Email template not found: {$templateName}");
            return false;
        }
        
        // Parse template variables
        $subject = $this->parseVariables($template['subject'], $data);
        $message = $this->parseVariables($template['message'], $data);
        $htmlMessage = $template['html_message'] ? $this->parseVariables($template['html_message'], $data) : null;
        
        return $this->send($to, $subject, $message, $htmlMessage);
    }
    
    /**
     * Send email
     */
    public function send($to, $subject, $message, $htmlMessage = null, $attachments = [])
    {
        // Execute pre-send hook
        $hookData = $this->hooks->execute('EmailPreSend', [
            'to' => $to,
            'subject' => $subject,
            'message' => $message,
            'html_message' => $htmlMessage
        ]);
        
        if (is_array($hookData)) {
            $to = $hookData['to'] ?? $to;
            $subject = $hookData['subject'] ?? $subject;
            $message = $hookData['message'] ?? $message;
            $htmlMessage = $hookData['html_message'] ?? $htmlMessage;
        }
        
        try {
            $mail = new PHPMailer(true);
            
            // Server settings
            $mail->isSMTP();
            $mail->Host = MAIL_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = MAIL_USERNAME;
            $mail->Password = MAIL_PASSWORD;
            $mail->SMTPSecure = MAIL_ENCRYPTION === 'tls' ? PHPMailer::ENCRYPTION_STARTTLS : PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port = MAIL_PORT;
            
            // Recipients
            $mail->setFrom(MAIL_FROM_EMAIL, MAIL_FROM_NAME);
            $mail->addAddress($to);
            
            // Content
            $mail->isHTML($htmlMessage !== null);
            $mail->Subject = $subject;
            $mail->Body = $htmlMessage ?? $message;
            
            if ($htmlMessage) {
                $mail->AltBody = $message;
            }
            
            // Attachments
            foreach ($attachments as $attachment) {
                if (is_array($attachment)) {
                    $mail->addAttachment($attachment['path'], $attachment['name'] ?? '');
                } else {
                    $mail->addAttachment($attachment);
                }
            }
            
            $mail->send();
            
            // Execute post-send hook
            $this->hooks->execute('EmailPostSend', [
                'to' => $to,
                'subject' => $subject,
                'success' => true
            ]);
            
            Logger::info("Email sent to {$to}: {$subject}");
            return true;
            
        } catch (\Exception $e) {
            Logger::error("Email sending failed: " . $e->getMessage());
            
            // Execute post-send hook with failure
            $this->hooks->execute('EmailPostSend', [
                'to' => $to,
                'subject' => $subject,
                'success' => false,
                'error' => $e->getMessage()
            ]);
            
            return false;
        }
    }
    
    /**
     * Parse template variables
     */
    private function parseVariables($text, $data)
    {
        // System variables
        $variables = [
            '{system_name}' => SYSTEM_NAME,
            '{system_url}' => SYSTEM_URL,
            '{company_name}' => SYSTEM_NAME,
            '{date}' => date('Y-m-d'),
            '{time}' => date('H:i:s'),
        ];
        
        // Add custom data
        foreach ($data as $key => $value) {
            if (is_scalar($value)) {
                $variables['{' . $key . '}'] = $value;
            } elseif (is_array($value)) {
                foreach ($value as $subKey => $subValue) {
                    if (is_scalar($subValue)) {
                        $variables['{' . $key . '_' . $subKey . '}'] = $subValue;
                    }
                }
            }
        }
        
        return str_replace(array_keys($variables), array_values($variables), $text);
    }
    
    /**
     * Queue email for later sending
     */
    public function queue($to, $subject, $message, $htmlMessage = null, $sendAt = null)
    {
        $this->db->insert('email_queue', [
            'to_email' => $to,
            'subject' => $subject,
            'message' => $message,
            'html_message' => $htmlMessage,
            'send_at' => $sendAt ?? date('Y-m-d H:i:s'),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
    
    /**
     * Process email queue
     */
    public function processQueue($limit = 50)
    {
        $emails = $this->db->fetchAll(
            "SELECT * FROM {$this->db->table('email_queue')} 
             WHERE status = 'pending' AND send_at <= NOW() 
             ORDER BY created_at ASC LIMIT ?",
            [$limit]
        );
        
        foreach ($emails as $email) {
            $success = $this->send(
                $email['to_email'],
                $email['subject'],
                $email['message'],
                $email['html_message']
            );
            
            $this->db->update(
                'email_queue',
                [
                    'status' => $success ? 'sent' : 'failed',
                    'sent_at' => $success ? date('Y-m-d H:i:s') : null,
                    'attempts' => $email['attempts'] + 1
                ],
                'id = ?',
                [$email['id']]
            );
        }
    }
}
