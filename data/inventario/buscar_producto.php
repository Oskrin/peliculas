<?php

session_start();
include '../../procesos/base.php';
conectarse();
$texto2 = $_GET['term'];

$consulta = pg_query("select * from productos T, bodega_productos P, bodegas B, usuario U where T.cod_productos = P.cod_productos and  P.id_bodega = B.id_bodega and U.id_bodega = B.id_bodega and U.id_usuario = '1' and T.articulo like '%$texto2%' and T.estado = 'Activo'");
while ($row = pg_fetch_row($consulta)) {
    $data[] = array(
        'value' => $row[3],
        'codigo' => $row[1],
        'codigo_barras' => $row[2],
        'precio' => $row[6],
        'stock' => $row[30],
        'p_venta' => $row[9],
        'existencia' => $row[22],
        'diferencia' => $row[23],
        'cod_producto' => $row[0]
    );
}

echo $data = json_encode($data);
?>
