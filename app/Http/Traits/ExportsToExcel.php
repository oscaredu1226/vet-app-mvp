<?php

namespace App\Http\Traits;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

/**
 * Trait para centralizar la lógica de exportación a Excel
 * Elimina código duplicado en múltiples controladores
 */
trait ExportsToExcel
{
    /**
     * Crea y configura una hoja de Excel con estilo estándar
     *
     * @param string $title Título del documento
     * @param array $headers Encabezados de columnas
     * @param array $data Datos a exportar (array de arrays)
     * @param string|null $fileName Nombre del archivo (opcional)
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportToExcel(string $title, array $headers, array $data, ?string $fileName = null)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Título
        $lastColumn = $this->getColumnLetter(count($headers) - 1);
        $sheet->setCellValue('A1', $title);
        $sheet->mergeCells("A1:{$lastColumn}1");
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Encabezados (fila 3)
        $sheet->fromArray($headers, null, 'A3');
        
        // Estilo de encabezados
        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ]
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ]
        ];
        $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray($headerStyle);

        // Datos (desde fila 4)
        $row = 4;
        foreach ($data as $rowData) {
            $col = 'A';
            foreach ($rowData as $value) {
                $sheet->setCellValue($col . $row, $value);
                $col++;
            }
            $row++;
        }

        // Aplicar bordes a toda la tabla
        if ($row > 4) {
            $sheet->getStyle("A3:{$lastColumn}" . ($row - 1))->applyFromArray([
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ]
            ]);
        }

        // Ajustar ancho de columnas automáticamente
        foreach (range('A', $lastColumn) as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $fileName = $fileName ?? ('export_' . date('Y-m-d_His') . '.xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Convierte un índice numérico a letra de columna de Excel
     * Ejemplo: 0 = A, 1 = B, 25 = Z, 26 = AA
     *
     * @param int $index
     * @return string
     */
    private function getColumnLetter(int $index): string
    {
        $letter = '';
        while ($index >= 0) {
            $letter = chr($index % 26 + 65) . $letter;
            $index = floor($index / 26) - 1;
        }
        return $letter;
    }

    /**
     * Exporta múltiples hojas a un archivo Excel
     *
     * @param array $sheets Array de hojas [['title' => '', 'headers' => [], 'data' => [], 'sheetName' => '']]
     * @param string|null $fileName Nombre del archivo
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    protected function exportMultipleSheets(array $sheets, ?string $fileName = null)
    {
        $spreadsheet = new Spreadsheet();

        foreach ($sheets as $index => $sheetData) {
            if ($index === 0) {
                $sheet = $spreadsheet->getActiveSheet();
            } else {
                $sheet = $spreadsheet->createSheet($index);
            }

            // Establecer nombre de la hoja
            if (!empty($sheetData['sheetName'])) {
                $sheet->setTitle($sheetData['sheetName']);
            }

            $title = $sheetData['title'];
            $headers = $sheetData['headers'];
            $data = $sheetData['data'];

            $lastColumn = $this->getColumnLetter(count($headers) - 1);

            // Título
            $sheet->setCellValue('A1', $title);
            $sheet->mergeCells("A1:{$lastColumn}1");
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Encabezados
            $sheet->fromArray($headers, null, 'A3');

            // Estilo de encabezados
            $headerStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4472C4']
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                    ]
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ]
            ];
            $sheet->getStyle("A3:{$lastColumn}3")->applyFromArray($headerStyle);

            // Datos
            $row = 4;
            foreach ($data as $rowData) {
                $col = 'A';
                foreach ($rowData as $value) {
                    $sheet->setCellValue($col . $row, $value);
                    $col++;
                }
                $row++;
            }

            // Aplicar bordes
            if ($row > 4) {
                $sheet->getStyle("A3:{$lastColumn}" . ($row - 1))->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                        ]
                    ]
                ]);
            }

            // Ajustar ancho de columnas
            foreach (range('A', $lastColumn) as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
        }

        // Volver a la primera hoja
        $spreadsheet->setActiveSheetIndex(0);

        // Generar archivo
        $writer = new Xlsx($spreadsheet);
        $fileName = $fileName ?? ('export_' . date('Y-m-d_His') . '.xlsx');
        $temp_file = tempnam(sys_get_temp_dir(), $fileName);

        $writer->save($temp_file);

        return response()->download($temp_file, $fileName)->deleteFileAfterSend(true);
    }
}
