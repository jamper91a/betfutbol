
<?php
foreach ($datos as $dato) {

//        echo "Fecha: ".$dato["Bet"]["fecha"]."</br>";
//        echo "Cantidad de ventas: ".$dato["Bet"]["cantidad"]."</br>";
//        echo "Cantidad de ingresos: ".$dato["Bet"]["ingresos"]."</br>";
}
?>

<?php
$acumulado = 0;
?>

<div class="mws-panel grid_3">
    <div class="mws-panel-header">
        <span class="mws-i-24 i-books-2">Detalles Diarios</span>
    </div>
    <div class="mws-panel-body">
        <ul class="mws-summary">
            <li>
                <span><?= $totalVentas ?></span> Cantidad Ventas
            </li>
            <li>
                <span><?= $ventasPagadas ?></span> Apuestas Pagadas
            </li>
            <li>
                <span><?= number_format($ingresos) ?></span> Ingresos
            </li>
            <li>
                <span><?= number_format($salidas) ?></span> Salidas
            </li>
            <li>
                <span><?= number_format($ingresos - $salidas) ?></span> Total
            </li>

        </ul>
    </div>
</div>

<div class="mws-panel grid_5">
    <div class="mws-panel-header">
        <span class="mws-i-24 i-table-1">Ventas</span>
    </div>
    <div class="mws-panel-body">
        <table class="mws-table" id="partidos">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tiquete</th>
                    <th>Apostado</th>
                    <th>Pagado</th>
                    <th>Fecha de Pago</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

                <?php foreach ($datos as $i => $dato): ?>
                    <?php if ($i % 2 == 0) { ?>
                        <tr class="gradeA even"  habilitado="true">
                        <?php } else { ?>
                        <tr class="gradeA odd"  habilitado="true">
                        <?php } ?>
                        <td> 
                            <?= $dato["Bet"]["created"] ?>
                        </td>
                        <td> 
                            <?= $dato["Bet"]["id"] ?>
                        </td>
                        <td> 
                            <?= $dato["Bet"]["apostado"] ?>
                        </td>
                        <td> 
                            <?php
                            if ($dato["Bet"]["pagado"] == 1) {
                                echo number_format($dato["Bet"]["ganancia"]);
                            } else {
                                echo "0";
                            }
                            ?>
                        </td>
                        <td> 
                            <?= $dato["Bet"]["fecha_pagado"] ?>
                        </td>
                        <td> 

                            <?= $this->Html->link(__('Estado'), array("controller" => "rows", 'action' => 'estado', $dato['Bet']['id'])) ?>
                        </td>

                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>