<?php
require_once 'PHPExcel.php';
require_once 'PHPExcel/IOFactory.php';

class My_PHPExcel_Excel extends PHPExcel {
	
	public $objPHPExcel;
	
	public function __construct() {
		$this->objPHPExcel = new PHPExcel();
	}
	
	public function combinar($rango, $texto) {
		$cell_text = explode(":", $rango);
		$this->objPHPExcel->getActiveSheet()->setCellValue($cell_text[0],$texto);
		$this->objPHPExcel->getActiveSheet()->mergeCells($rango);
		$this->setBordersThin($rango);
	}
	
	public function createExcel($file_name = null, $columns_name = array(), $data = array(), $font_size = null, $titulo = array(), $nombre = '') {
		
		$f_size = 10;
		
		if($font_size != null) {
			$f_size = $font_size;
		}
		
		//Fuente y tamaño de fuente
		$this->objPHPExcel->getDefaultStyle()->getFont()->setName('Arial');
		$this->objPHPExcel->getDefaultStyle()->getFont()->setSize($f_size);
		$this->objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setWrapText(true);
		$this->objPHPExcel->getActiveSheet()->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
		
		//Instancia para insertar la imagen/logotipo en la hoja	
		$objDrawing = new PHPExcel_Worksheet_Drawing();
			$objDrawing->setPath('./imagenes/logo.png');
			$objDrawing->setWidth(100);
			$objDrawing->setHeight(100);
			$objDrawing->setWorksheet($this->objPHPExcel->getActiveSheet());
		
		
		/*Merge Cells*/
		
		if(!empty($titulo)) {
			
			$cell_text = explode(":", $titulo['rango']);
			$this->objPHPExcel->getActiveSheet()->setCellValue($cell_text[0],$titulo['texto']);
			$this->objPHPExcel->getActiveSheet()->mergeCells($titulo['rango']);
			
			if($titulo['borde']) {
				$this->setBorders($titulo['rango']);
			}
			
			if(!isset($titulo['size']) || $titulo['size'] == "" || !is_numeric($titulo['size'])) {
				$tam_fuente = 12;
			} 
			else {
				$tam_fuente = $titulo['size'];
			}
			
			$this->objPHPExcel->getActiveSheet()->getStyle($titulo['rango'])->getFont()->setBold(true);
			$this->objPHPExcel->getActiveSheet()->getStyle($titulo['rango'])->getFont()->setSize($tam_fuente);
			$this->objPHPExcel->getActiveSheet()->getStyle($cell_text[0])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
		}
		
		//Poner los nombres de cada columna
		foreach($columns_name as $cell => $column) {
			
			$this->objPHPExcel->getActiveSheet()->getColumnDimension(substr($cell, 0, 1))->setWidth($column['width']);
			$this->setBorders($cell);
			$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getFont()->setBold(true);
			$this->objPHPExcel->getActiveSheet()->setCellValue($cell,$column['name']);
			
		}
		
		//Vaciar la información a la hoja de cálculo
		foreach($data as $data) {
			
			foreach($data as $cell => $value) {
				$this->objPHPExcel->getActiveSheet()->setCellValue($cell,$value);
				$this->objPHPExcel->getActiveSheet()->getRowDimension("'".substr($cell, 1, 2)."'")->setRowHeight(-1);
				$this->setBordersThin($cell);
			}
		}
		
		//Nombrar hoja
		$this->objPHPExcel->getActiveSheet()->setTitle($file_name);
		
		//Guardamos el archivo y forzamos la descarga
		ob_end_clean();
		
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		
		if($nombre != '')
			$objWriter->save($nombre);
		else {
			header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
			header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			header('Content-Disposition: attachment;filename="'.utf8_decode($file_name).'.xlsx"');
		
			$objWriter->save("php://output");
		}
		
		ob_end_clean();
	}
	
	//Función para poner borde gruso a la celda
	public function setBorders($cell) {
		
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
	}
	
	//Función para poner borde delgado a la celda
	public function setBordersThin($cell) {
		
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
		$this->objPHPExcel->getActiveSheet()->getStyle($cell)->getBorders()->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);
	}
}
