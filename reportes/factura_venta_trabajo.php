<?php
    //require('../fpdf/fpdf.php');
    include '../fpdf/rotation.php';
    include '../procesos/base.php';
    include '../procesos/funciones.php';
    include '../procesos/convertir.php';

    conectarse();    
    date_default_timezone_set('America/Guayaquil'); 
    session_start()   ;
    class PDF extends PDF_Rotate {   
        var $widths;
        var $aligns;
        function SetWidths($w) {            
            $this->widths=$w;
        }    

        function RotatedText($x, $y, $txt, $angle) {
            //Text rotated around its origin
            $this->Rotate($angle, $x, $y);
            $this->Text($x, $y, $txt);
            $this->Rotate(0);
        }

        function RotatedImage($file, $x, $y, $w, $h, $angle) {
            //Image rotated around its upper-left corner
            $this->Rotate($angle, $x, $y);
            $this->Image($file, $x, $y, $w, $h);
            $this->Rotate(0);
        }                      
    }
    // $pdf = new PDF('L','mm',array(200,210));
    $pdf = new PDF('P','mm','a4');
    $pdf->AddPage();
    $pdf->SetMargins(0,0,0,0);
    $pdf->AliasNbPages();
    $pdf->AddFont('Amble-Regular','','Amble-Regular.php');
    $pdf->SetFont('Amble-Regular','',10);       
    $pdf->SetFont('Arial','B',9);   
    $pdf->SetX(5);    
    $pdf->SetFont('Amble-Regular','',9);     

    $sql = pg_query("select id_factura_venta, num_factura,fecha_actual, tarifa0,tarifa12,iva_venta,descuento_venta,total_venta,clientes.id_cliente,identificacion,nombres_cli,direccion_cli,telefono,clientes.ciudad,forma_pago,factura_venta.estado from factura_venta,clientes where id_factura_venta = '".$_GET['id']."' and factura_venta.id_cliente = clientes.id_cliente");
    while($row = pg_fetch_row($sql)) {
        $id_cliente = $row[8];
        $cliente = $row[10];
        $ci_ruc = $row[9];
        $direccion = $row[11];
        $telefono = $row[12];
        $ciudad = $row[13];
        $pago = $row[14];
        $fecha = $row[2];
        $nro_fac = substr($row[1],8);
        $iva0 = $row[3];
        $iva12 = $row[4];
        $iva_venta = $row[5];
        $descuento_venta = $row[6];
        $total_venta = $row[7];
        $estado = $row[15];
    }      

    // Cabezera Factura  
    $pdf->SetFont('Arial','B',10);        
    $pdf->SetFont('Amble-Regular','',10);       
    $pdf->Text(30, 77, maxCaracter(utf8_decode($cliente),50),1,0, 'L',0);/////cliente
	$pdf->Text(30, 82, maxCaracter(utf8_decode($ci_ruc),20),1,0, 'L',0);/////identificacion
    $pdf->Text(30, 87, maxCaracter(utf8_decode($direccion),35),1,0, 'L',0);////direccion
    $pdf->Text(30, 93, maxCaracter(utf8_decode($telefono),35),1,0, 'L',0);////telefono
    $pdf->Text(88, 92, maxCaracter(utf8_decode($ciudad),35),1,0, 'L',0);////ciudad
    $pdf->Text(165, 77, maxCaracter(utf8_decode($fecha),20),1,0, 'L',0);////fecha
    $pdf->Text(165, 82, maxCaracter(utf8_decode($pago),20),1,0, 'L',0);////forma pago
        
    if($estado == 'Pasivo') {        
        $pdf->SetTextColor(249,33,33);
        $pdf->RotatedImage('../images/circle.png', 150, 42, 30, 10, 45);        
        $pdf->RotatedText(160,41, 'ANULADO!', 45);      
    }

    // detalles factura
    $sql = pg_query("select cantidad,articulo,precio_venta,total_venta from  detalle_factura_venta,productos where id_factura_venta = '".$_GET['id']."' and detalle_factura_venta.cod_productos = productos.cod_productos and productos.incluye_iva= 'Si'");
    $yy = 110;
    $iva_base = 1.12;    
    $pdf->SetTextColor(0,0,0);

    while($row = pg_fetch_row($sql)) {
        $total_si = 0;
        $total_sit = 0;
        $total_si = $row[3] / $iva_base;
        $total_sit = $total_si / $row[0];
        $total_si = truncateFloat($total_si,2);
        $total_sit = truncateFloat($total_sit,2);

        // posiciones cantidad
        $pdf->Text(15, $yy, maxCaracter(utf8_decode($row[0]),3),0,1, 'L',0);            
        
        $array = ceil_caracter($row[1],35);
        if(sizeof($array) > 1){
            $zz = $yy;
            for($i = 0; $i < sizeof($array); $i++){
                $pdf->Text(20, $zz, utf8_decode($array[$i]),0,0, 'J',0);                               
                        $zz = $zz + 3;
            }
            $yy = $yy + 4;
        } else {
            $pdf->Text(30, $yy, maxCaracter(utf8_decode($row[1]),30),0,0, 'L',0);                           
        }                            
        // posiciones precio unitario
        $pdf->Text(150, $yy, maxCaracter(number_format($total_sit,2,',','.'),6),0,0, 'L',0);            
        // posiciones valor total
        $pdf->Text(185, $yy, maxCaracter(number_format($total_si,2,',','.'),6),0,0, 'L',0);                                    
        $yy = $yy + 4;    
    }

    $letras=new EnLetras();
    $sql = pg_query("select cantidad,articulo,precio_venta,total_venta from  detalle_factura_venta,productos where id_factura_venta = '".$_GET['id']."' and detalle_factura_venta.cod_productos = productos.cod_productos and productos.incluye_iva= 'No'");    
    $pdf->SetTextColor(0,0,0);
    while($row = pg_fetch_row($sql)) {
        $temp_1 =  number_format($row[3],2,',','.');        
        $pdf->Text(5, $yy, maxCaracter(utf8_decode($row[0]),3),0,1, 'L',0);                                                    
        
        $array = ceil_caracter($row[1],35);
        if(sizeof($array) > 1){
            $zz = $yy;
            for($i = 0; $i < sizeof($array); $i++){
                $pdf->Text(20, $zz, utf8_decode($array[$i]),0,0, 'J',0);                               
                        $zz = $zz + 3;
            }
            $yy = $yy + 4;
        } else {
            $pdf->Text(20, $yy, maxCaracter(utf8_decode($row[1]),30),0,0, 'L',0);                           
        }    
        $pdf->Text(150, $yy, maxCaracter(utf8_decode($row[2]),6),0,0, 'L',0);    
                
        $pdf->Text(177, $yy, maxCaracter($temp_1,6),0,0, 'L',0);                                    
        $yy = $yy + 4;                                                
    }

    // Pie de Factura       
    $subtotal = truncateFloat($iva12,2);
    $descuento_venta = truncateFloat($descuento_venta,2);
    $iva_venta = truncateFloat($iva_venta,2);
    $iva0 = truncateFloat($iva0,2);
    $total_venta = truncateFloat($total_venta,2);

    $pdf->Text(25, 238, maxCaracter($letras->ValorEnLetras($total_venta,"dolares"),60),0,1, 'L',0);
    $pdf->Text(180, 240, maxCaracter($subtotal,5),0,1, 'L',0);    
    $pdf->Text(180, 247, maxCaracter($descuento_venta,5),0,1, 'L',0);     
    $pdf->Text(180, 254, maxCaracter('0.00',5),0,1, 'L',0);    
    $pdf->Text(180, 261, maxCaracter($iva0,5),0,1, 'L',0);    
    $pdf->Text(180, 268, maxCaracter($iva_venta,10),0,1, 'L',0);
    $pdf->Text(180, 278, maxCaracter($total_venta,10),0,1, 'L',0);    

    $pdf->Output();
?>