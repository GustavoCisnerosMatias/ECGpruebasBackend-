<?php
require_once __DIR__ . '/../lib/tcpdf/tcpdf.php';
require_once 'lib\middleware\JwtMiddleware.php';

class pdf_Ctrl {

    public function imagepolyline($img, $points, $color) {
        for ($i = 2; $i < count($points); $i += 2) {
            imageline($img, $points[$i - 2], $points[$i - 1], $points[$i], $points[$i + 1], $color);
        }
    }
    public function generarPDF($f3) {
        try {
            $data = json_decode($f3->get('BODY'), true);
            if (
                json_last_error() !== JSON_ERROR_NONE ||
                !isset($data['nombre_usuario'], $data['valores'], $data['duracion'], $data['id_dispo']) ||
                !is_array($data['valores'])
            ) {
                throw new \Exception('Payload inválido. Debe incluir nombre_usuario, valores[], duracion e id_dispo.');
            }
    
            $nombreUsuario = $data['nombre_usuario'];
            $valores = array_map('floatval', $data['valores']);
            $duracion = max(1, (int)$data['duracion']); // Asegura que nunca sea cero
            $idDispositivo = $data['id_dispo'];
    
            $nSamples = count($valores);
            if ($nSamples < 2) {
                throw new \Exception('Se requieren al menos 2 valores para graficar.');
            }
    
            $rate = $nSamples / $duracion;
            $windowSec = 5;
    
            $tmpDir = __DIR__ . '/temp_images/';
            if (!is_dir($tmpDir)) {
                mkdir($tmpDir, 0755, true);
            }
    
            $pdf = new TCPDF('L','mm','A4', true,'UTF-8', false);
            $pdf->SetMargins(15, 20);
    
            $pages = (int)ceil($duracion / $windowSec);
    
            for ($p = 0; $p < $pages; $p++) {
                $t0 = $p * $windowSec;
                $t1 = min(($p + 1) * $windowSec, $duracion);
    
                $i0 = (int)floor($t0 * $rate);
                $i1 = (int)ceil($t1 * $rate);
    
                $segment = array_slice($valores, $i0, max(1, $i1 - $i0));
    
                if (count($segment) < 2) {
                    continue; // No generar página si hay menos de 2 puntos
                }
    
                $ancho = 600; $alto = 300;
                $img = imagecreatetruecolor($ancho, $alto);
                $blanco = imagecolorallocate($img, 255, 255, 255);
                $rojo = imagecolorallocate($img, 255, 0, 0);
                imagefill($img, 0, 0, $blanco);
    
                $minV = min($segment);
                $maxV = max($segment);
                $rango = ($maxV - $minV) ?: 1;
    
                $puntos = [];
                $m = count($segment);
                for ($i = 0; $i < $m; $i++) {
                    $x = ($m == 1) ? $ancho / 2 : ($i / ($m - 1)) * $ancho;
                    $y = $alto - ((($segment[$i] - $minV) / $rango) * $alto);
                    $puntos[] = $x;
                    $puntos[] = $y;
                }
    
                imagefilledrectangle($img, 0, 0, $ancho, $alto, $blanco);
                imageantialias($img, true);
                $this->imagepolyline($img, $puntos, $rojo);
    
                $imgPath = $tmpDir . uniqid("ecg_p{$p}_") . '.png';
                imagepng($img, $imgPath);
                imagedestroy($img);
    
                $pdf->AddPage();
                $pdf->SetFont('helvetica','B',14);
                $pdf->Cell(0,8, "Historial ECG - $nombreUsuario",0,1,'C');
                $pdf->SetFont('helvetica','',12);
                $pdf->Cell(0,6, "ID Dispo: $idDispositivo | Segmento: ".($t0)."s - ".($t1)."s",0,1,'C');
    
                $pdf->Ln(4);
                $pdf->Image($imgPath, 15, 40, 260, 90, 'PNG');
                @unlink($imgPath);
            }
    
            header('Content-Type: application/pdf');
            header('Content-Disposition: inline; filename="Historial_ECG_'.$nombreUsuario.'.pdf"');
            echo $pdf->Output('', 'S');
            exit;
    
        } catch (\Exception $e) {
            $this->generateErrorPdf($e->getMessage());
            exit;
        }
    }
    
    
    private function generateErrorPdf($msg = 'Error generando PDF') {
        $pdf = new TCPDF('L','mm','A4', true,'UTF-8', false);
        $pdf->AddPage();
        $pdf->SetFont('helvetica','B',16);
        $pdf->Cell(0,10, 'ERROR',0,1,'C');
        $pdf->SetFont('helvetica','',12);
        $pdf->MultiCell(0,8, $msg);
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="Error.pdf"');
        echo $pdf->Output('', 'S');
    }

}
