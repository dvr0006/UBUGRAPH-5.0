<!DOCTYPE HTML>
<?php
    //Comprobamos que el usuario esta logueado
    session_start();
    if (!isset($_SESSION["usuario"]))
    { 
        header("Location: /");
    }
    require_once("funciones.php");
    require_once("StandardNormal.php");
    //Cargamos el idioma
    require("../".idioma());
?>
<html>
    <head>
        <link rel="stylesheet" type="text/css"  href="/estilos/estilo.css">     
    </head>
    <body>  
        <div id="main">
            <a href='http://davidmlane.com/hyperstat/z_table.html' target="_blank"><center><?php echo$texto["Tabla_normal_3"] ?></center></a><br>
            <h2 style="text-align: center;"><?php echo $texto["Tabla_normal_1"] ?></h2>
            <table style="width: 100%; text-align: center;">
                <tr>
                    <th>Z</th>
                    <th>0</th>
                    <th>1</th>
                    <th>2</th>
                    <th>3</th>
                    <th>4</th>
                    <th>5</th>
                    <th>6</th>
                    <th>7</th>
                    <th>8</th>
                    <th>9</th>
                </tr>
                <?php
                    for($i = 35; $i < sizeof(StandardNormal::$Z_SCORES); $i++){
                        $fila=round(-3.4+($i-1)*0.1,1);
                        $fila=sprintf('%1.1f',$fila);
                        echo "<tr>";
                        echo "<td><b>{$fila}</b></td>";
                        for($j = 0; $j < 10; $j++){
                            echo "<td>";
                            $dato=StandardNormal::$Z_SCORES[$fila][$j];
                            echo $dato;
                            echo "</td>";
                        }
                        echo "</tr>";
                    }
                ?>
            </table>
            <h2 style="text-align: center;"><?php echo $texto["Tabla_normal_2"] ?></h2>
            <center>
            <table style="width: 20%; text-align: center;">
                <tr>
                    <th>%</th>
                    <th>Z</th>
                </tr>
                <?php
                    for($i = 1; $i <= sizeof(StandardNormal::$Z_SCORES_FOR_CONFIDENCE_INTERVALS); $i++){
                        $clave=StandardNormal::$probabilidadAleatoria[$i];
                        $valorZ=StandardNormal::$Z_SCORES_FOR_CONFIDENCE_INTERVALS[$clave];
                        echo "<tr>";
                        echo "<td><b>{$clave}</b></td>";
                        echo "<td>{$valorZ}</td>";
                        echo "</tr>";
                    }
                ?>
            </table>
            </center>
        </div>
    </body>
</html>