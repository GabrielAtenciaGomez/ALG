<?php
// Import PHPMailer classes into the global namespace
// These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'vendor/autoload.php';
require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
require 'recaptchalib.php';

//echo 'Más información de depuración ';
// print_r($_FILES);

function SubirArchivo()
{
    $dir_subida = 'imagencotizar/';
    $fichero_subido = $dir_subida . basename($_FILES['imagen']['name']);
    //   echo $fichero_subido . ' <br>';


    if (move_uploaded_file($_FILES['imagen']['tmp_name'], $fichero_subido)) {
        return $fichero_subido;
    } else {
        return "";
    }
}

$recaptcha_secret = '6Le7KdoUAAAAAO1R3nVlc4b2nxUAQFKA2KOwHEXw';
$recaptcha_response = $_POST['recaptcha_response'];
$url = 'https://www.google.com/recaptcha/api/siteverify';

//echo $recaptcha_response . '<br>';

$data = array('secret' => $recaptcha_secret, 'response' => $recaptcha_response, 'remoteip' => $_SERVER['REMOTE_ADDR']);
$curlConfig = array(CURLOPT_URL => $url, CURLOPT_POST => true, CURLOPT_RETURNTRANSFER => true, CURLOPT_POSTFIELDS => $data);
$ch = curl_init();
curl_setopt_array($ch, $curlConfig);
$response = curl_exec($ch);
curl_close($ch);

$jsonResponse = json_decode($response);

if ($jsonResponse->success === true) {
    // Código para procesar el formulari
    //  echo 'entrooo'; 
    // Instantiation and passing `true` enables exceptions

    $mail = new PHPMailer();

    $nombre = $_POST['recipient-name'];
    $apellidos = $_POST['recipient-apellido'];
    $telefono = $_POST['recipient-telefono'];
    $correo = $_POST['recipient-correo'];
    $reduccion = $_POST['recipient-reduccion'];
    $mensaje = $_POST['message-text'];
    $sImagen = SubirArchivo();




    try {
        //Server settings
        $mail->SMTPDebug = 0;                      // Enable verbose debug output
        $mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = "smtp.gmail.com";                    // Set the SMTP server to send through
        $mail->SMTPAuth   = true;                                   // Enable SMTP authentication
        $mail->Username   = "cotizar.alg@gmail.com";                     // SMTP username
        $mail->Password   = "123456789alg";                               // SMTP password
        $mail->SMTPSecure = "tls";         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;
        //$mail->SMTPOptions = array(
    //   'ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true)
      //  );                       // TCP port to connect to

        //Recipients
        $mail->setFrom('cotizar.alg@gmail.com');
        $mail->addAddress('ing.emir.alg@gmail.com');     // Add a recipient





        // Content
        $mail->isHTML(true);                                  // Set email format to HTML
        $mail->Subject = 'Cotizacion para ' . $nombre . ' ' . $apellidos;
        $mail->AddEmbeddedImage($sImagen, 'imagen');

        $mail->Body    = 'Cotizacion solicitada por: <b>' . $nombre . ' ' . $apellidos .  '</b>  <br>
    <b>correo: </b>' .  $correo .
            '<br><b>Telefono: </b>' .  $telefono .
            '<br><b>reduccion: </b>' .  $reduccion .
            '<br><b>Mensaje: </b>' .  $mensaje;

        $mail->send();
        

        

        header("refresh:3 ;http://www.algmexico.com");
        echo 'Message has been sent';
        echo '<br> Mensaje enviado sera redireccionado en 3 segundos a la pagina principal, Muchas gracias por confiar en nosotros';

        //header ("Location: http://www.algmexico.com");
        
    } catch (Exception $e) {
        echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
} else {

    // código para lanzar aviso de error en el envío
    echo 'span detectado y sera redirecionado a la pagina principal';
    header("refresh:3;http://www.algmexico.com");
}
