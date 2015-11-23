<?php

session_start();
include '../../procesos/base.php';
conectarse();
error_reporting(0);

/////datos detalle factura/////
$campo1 = $_POST['campo1'];
$campo2 = $_POST['campo2'];
$campo3 = $_POST['campo3'];
$campo4 = $_POST['campo4'];
$campo5 = $_POST['campo5'];
///////////////////////////////
//
/////////////////contador ingresos///////////
$cont1 = 0;
$consulta = pg_query("select max(id_egresos) from egresos");
while ($row = pg_fetch_row($consulta)) {
    $cont1 = $row[0];
}
$cont1++;
//////////////////////////////////////////////////
//
////////////guardar egresos////////
pg_query("insert into egresos values('$cont1','1','$_SESSION[id]','$cont1','$_POST[fecha_actual]','$_POST[hora_actual]','$_POST[origen]','$_POST[destino]','$_POST[tarifa0]','$_POST[tarifa12]','$_POST[iva]','$_POST[desc]','$_POST[tot]','$_POST[observaciones]','Activo')");
////////////////////////////////////////
//
////////////agregar ingresos////////
$arreglo1 = explode('|', $campo1);
$arreglo2 = explode('|', $campo2);
$arreglo3 = explode('|', $campo3);
$arreglo4 = explode('|', $campo4);
$arreglo5 = explode('|', $campo5);
$nelem = count($arreglo1);

///////////////////////////////////////////
for ($i = 0; $i <= $nelem; $i++) {
    // contador detalle egreso
    $cont2 = 0;
    $consulta = pg_query("select max(id_detalle_egreso) from detalle_egreso");
    while ($row = pg_fetch_row($consulta)) {
        $cont2 = $row[0];
    }
    $cont2++;
    // fin

    // contador bodegas productos
    $cont_b = 0;
    $consulta_b = pg_query("select max(id_bodega_productos) from bodega_productos");
    while ($row = pg_fetch_row($consulta_b)) {
        $cont_b = $row[0];
    }
    $cont_b++;
    // fin

    // contador kardex
    $cont_k = 0;
    $consulta_k = pg_query("select max(id_kardex) from kardex");
    while ($row = pg_fetch_row($consulta_k)) {
        $cont_k = $row[0];
    }
    $cont_k++;
    // fin
    
    // guardar detalle egreso
    pg_query("insert into detalle_egreso values('$cont2','$cont1','$arreglo1[$i]','$arreglo2[$i]','$arreglo3[$i]','$arreglo4[$i]','$arreglo5[$i]','Activo')");
    // fin 
    
    // modificar productos general
    $consulta2 = pg_query("select * from productos where cod_productos = '$arreglo1[$i]'");
    while ($row = pg_fetch_row($consulta2)) {
        $stock = $row[13];
    }
    $cal = $stock - $arreglo2[$i];
    
    pg_query("Update productos Set stock='" . $cal . "' where cod_productos='" . $arreglo1[$i] . "'");
    // fin

    // comparar guardados
    $consulta_repe = pg_query("select count(*) from bodega_productos where cod_productos = '$arreglo1[$i]' and id_bodega = '$_POST[destino]'");
    while ($row = pg_fetch_row($consulta_repe)) {
        $resp =   $row[0];
    }

    if($resp == 1) {
        // modificar productos bodega origen resta
        $consulta3 = pg_query("select * from bodega_productos where cod_productos = '$arreglo1[$i]' and id_bodega = '$_POST[origen]'");
        while ($row = pg_fetch_row($consulta3)) {
            $stock2 = $row[3];
        }
        $cal2 = $stock2 - $arreglo2[$i];
        
        pg_query("Update bodega_productos Set stock_bodega='" . $cal2 . "' where cod_productos='" . $arreglo1[$i] . "' and id_bodega = '$_POST[origen]'");
        // fin 

        // modificar productos bodega destino suma
        $consulta3 = pg_query("select * from bodega_productos where cod_productos = '$arreglo1[$i]' and id_bodega = '$_POST[destino]'");
        while ($row = pg_fetch_row($consulta3)) {
                $stock3 = $row[3];
        }
        $cal3 = $stock3 + $arreglo2[$i];
        
        pg_query("Update bodega_productos Set stock_bodega='" . $cal3 . "' where cod_productos='" . $arreglo1[$i] . "' and id_bodega = '$_POST[destino]'");
    } else {
        // guardar bodegas productos
        pg_query("insert into bodega_productos values('$cont_b','$arreglo1[$i]','$_POST[destino]','$arreglo2[$i]','Activo')");
        // fin

        // modificar productos bodega origen resta
        $consulta3 = pg_query("select * from bodega_productos where cod_productos = '$arreglo1[$i]' and id_bodega = '$_POST[origen]'");
        while ($row = pg_fetch_row($consulta3)) {
            $stock2 = $row[3];
        }
        $cal2 = $stock2 - $arreglo2[$i];
        
        pg_query("Update bodega_productos Set stock_bodega='" . $cal2 . "' where cod_productos='" . $arreglo1[$i] . "' and id_bodega = '$_POST[origen]'");
        // fin
    }
    
    // guardar kardex
    pg_query("insert into kardex values('$cont_k','$_POST[fecha_actual]', '" . 'T.E:' . $cont1 . "' ,'$arreglo2[$i]','$arreglo3[$i]','$arreglo5[$i]','$arreglo1[$i]','$cal','4','$_POST[origen]','$_POST[destino]')");
    // fin
}

$data = 1;
echo $data;
?>
