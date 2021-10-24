<?php


?>
<!DOCTYPE html>
<html>
<head>
    <title>Page Title</title>
    <script
        src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
        crossorigin="anonymous"></script>
</head>
<body>
<?php



$cln        =  $_GET['cln'];
$order_id   =$_GET['order_id'];
$session_id = $_GET['session_id'];
$additionalData = $_GET['additionalData'];


?>


<form action="https://quickpay.sd/cpayment/exec/chechout" method="post" id="formId" >
    <input type="hidden" name="cln" value="<?php echo $cln; ?>"><br>
    <input type="hidden" name="order_id" value="<?php echo $order_id; ?>"><br>
    <input type="hidden" name="session_id" value="<?php echo $session_id; ?>"><br>
    <input type="hidden" name="additionalData" value="<?php echo $additionalData; ?>"><br>
</form>


<script type="text/javascript">
    $( document ).ready(function() {
        console.log( "ready!" );
        document.getElementById('formId').submit();
    });

</script>
</body>
</html>