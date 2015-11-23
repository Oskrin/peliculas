<?php

session_start();
include '../../procesos/base.php';
conectarse();
$texto2 = $_GET['term'];

$consulta = pg_query("select * from productos T, bodega_productos P, bodegas B, usuario U where T.cod_productos = P.cod_productos and  P.id_bodega = B.id_bodega and U.id_bodega = B.id_bodega and U.id_usuario = '1' and T.articulo like '%$texto2%' and T.estado = 'Activo'");
while ($row = pg_fetch_row($consulta)) {
    $data[] = array(
        'value' => $row[3],
        'codigo_barras' => $row[2],
        'codigo' => $row[1],
        'precio' => $row[6],
        'p_venta' => $row[9],
        'iva_producto' => $row[4],
        'cod_producto' => $row[0],
        'incluye' => $row[26],
        'disponibles' => $row[30]
    );
}

echo $data = json_encode($data);
?>
