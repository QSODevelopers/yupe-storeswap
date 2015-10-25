<?php 
/**
* Класс для сборки excel-прайса
*/
Yii::import('application.modules.store.components.ProductRepository');

class ExcelCreator extends CComponent
{
	/**
	 * Указатель на активную строку куда производится запись
	 * @var int
	 */
	private $activeRow;
	/**
	 * Текущий уровень каталога
	 * @var int
	 */
	private $activeLevel;
	/**
	 * @var ProductRepository
	 */
	private $productRepository;
	/**
	 * @var XPHPExcel
	 */
	private $objPHPExcel;
	/**
	 * Активный лист куда пишем каталог
	 */
	private $activeList;


	public function init(){
		$this->productRepository = new ProductRepository();
		$this->objPHPExcel = XPHPExcel::createPHPExcel();
		$this->activeList = $this->objPHPExcel->setActiveSheetIndex(0);
		$this->activeRow = 1;
		$this->activeLevel = 0;
	}

	public function createExcelFile($file_name){
		$this->init();
		
		//Add some headers
		$this->objPHPExcel->getProperties()->setCreator("Yupe! UnnamedTeam")
							 ->setLastModifiedBy("Yupe! UnnamedTeam")
							 ->setTitle("БКТ. Прайс-лист товаров от ".Yii::app()->dateFormatter->format('dd.mm.yyyy',time()))
							 ->setSubject("Office 2007 XLSX Test Document")
							 ->setKeywords("БКТ прайс-лист")
							 ->setCategory("БКТ");

		//Rename worksheet
		$this->activeList->setTitle('Прайс-лист');

		//Add some header data
		$this->activeList
					->setCellValue('A1', 'Прайс-лист')
					->setCellValue('A3', 'ООО "БумКанцТорг"')
					->setCellValue('A4', 'Адрес: 460009, Оренбургская обл, Оренбург, Пролетарская, дом № 135, тел.: (3532) 56-17-62')
					->setCellValue('A5', 'В валютах цен.')
					->setCellValue('A6', 'Цены указаны на '.Yii::app()->dateFormatter->format('dd.mm.yyyy',time()));

		//Create table header
		$this->activeList->getColumnDimension('A')->setWidth(75);
		$this->activeList->getColumnDimension('B')->setWidth(20);
		$this->activeList->getColumnDimension('C')->setWidth(15);
		$this->activeList
					->mergeCells('A9:A10')
					->setCellValue('A9', 'Ценовая группа/ Номенклатура/ Характеристика')
					->mergeCells('B9:B10')
					->setCellValue('B9', 'Номенклатура.Артикул')
					->mergeCells('C9:D9')
					->setCellValue('C9', 'Розничные')
					->setCellValue('C10', 'Цены')
					->setCellValue('D10', 'Ед.');

		//Меняем активную клетку на 11, все остальное было шапкой			
		$this->activeRow = 11;  
		
		//Create categories and products
		//получаем массив всех категорий в виде дерева
		$data = StoreCategory::model()->getMenuList(10); 
		if (!$this->createCatalogTree($data))
			throw new CHttpException(
				400,
				Yii::t('Exchange1cModule.main', "Произошла ошибка во время заполнения листа продуктами")
			);

		if (!$this->stylingSheet())
			throw new CHttpException(
				400,
				Yii::t('Exchange1cModule.main', "Произошла ошибка во время выполнения функции декорирования листа")
			);
		
		$this->objPHPExcel->setActiveSheetIndex(0);
		
		//Write in file          
		$objWriter = PHPExcel_IOFactory::createWriter($this->objPHPExcel, 'Excel2007');
		$objWriter->save(Yii::getPathOfAlias('public').'/uploads/files/store/'.$file_name);
		return true;
	}

	/**
	 * Формирование списка категорий с товарами
	 * @param  array $data массив категорий
	 * @return boolean
	 */
	private function createCatalogTree(array $data){
		$this->activeLevel++;
		foreach ($data as $node) {
			//Добавление категории и декорирование ячейки
			$this->activeList
					->mergeCells('A'.$this->activeRow.':D'.$this->activeRow)
					->setCellValue('A'.$this->activeRow, $this->addTabs($node['label']))
					->getStyle('A'.$this->activeRow.':D'.$this->activeRow)
					->applyFromArray($this->getLevelStyle());

			$this->activeRow++;

			if (count($node['items'])) {
				//Если есть подкатегории используем рекурсию 
				$this->createCatalogTree($node['items']);
			}else{
				//В противном случае тащим все товары категории
				$storeCategory = StoreCategory::model()->findByPk($node['id']);
				$dataProvider = $this->productRepository->getListForCategory($storeCategory);
				//И пишем их в таблицу
				foreach ($dataProvider->data as $product) {
					$this->writeProductRow($product);
					$this->activeRow++;
				}
			}
		}
		$this->activeLevel--;
		return true;
	}

	/**
	 * Запись продукта в строку таблицы
	 * @param Product $product объект класса Product
	 */
	private function writeProductRow(Product $product){
		$this->activeList
				->setCellValue('A'.$this->activeRow, $this->addTabs($product->name))
				->setCellValue('B'.$this->activeRow, $product->sku)
				->setCellValue('C'.$this->activeRow, $product->price.' руб.')
				->setCellValue('D'.$this->activeRow, 'шт.');
	}

	/**
	 * Изменение внешнего вида листа, декорируем под нужды заказчика
	 * @return boolean
	 */
	private function stylingSheet(){
		//Заголовок в первой ячейке ==Прайс-лист==
		$this->activeList
					->getStyle('A1')
					->applyFromArray([
						'font' => [
								'bold' => true,
								'italic'=> true,
								'size' => 32,
							]
					]);
		//Заголовок в третьей ячейке ==ООО "БумКанцТорг"==
		$this->activeList
					->getStyle('A3')
					->applyFromArray([
						'font' => [
								'bold' => true,
								'size' => 14,
							]
					]);
		//Строка адреса в четвертой ячейке ==Адрес: 460009, Оренбур==
		$this->activeList
					->getStyle('A4')
					->applyFromArray([
						'font' => [
								'bold' => true,
								'size' => 10,
							]
					]);
		//Мелкий текст далече
		$this->activeList
					->getStyle('A5:A6')
					->applyFromArray([
						'font' => [
								'size' => 8,
							]
					]);
		//Шапка таблицы
		$this->activeList
				->getStyle('A9:D10')
				->applyFromArray(
					[
						'font' => [
							'bold' => true,
							'size' => 9,
						],
						'alignment' => [
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_CENTER,
							'vertical' => PHPExcel_Style_Alignment::VERTICAL_CENTER,
						],
						'borders' => [
							'allborders' => [
								'style' => PHPExcel_Style_Border::BORDER_THIN,
							],
						],
						'fill' => [
							'type' => PHPExcel_Style_Fill::FILL_SOLID,
							'startcolor' => [
								'argb' => 'FFBBBBBB',
							],
						],
					]
				);
		//Последнии три столбца выравниваем по праву
		$this->activeList
				->getStyle('B11:'.$this->getLastCell($this->activeList->calculateWorksheetDimension()))
				->applyFromArray(
					[
						'alignment' => [
							'horizontal' => PHPExcel_Style_Alignment::HORIZONTAL_RIGHT,
						]
					]
				);
		//Чертим у таблицы рамку и уменьшаем шрифт
		$this->activeList
				->getStyle('A9:'.$this->getLastCell($this->activeList->calculateWorksheetDimension()))
				->applyFromArray(
					[
						'font'=>[
							'size'=> 8
						],
						'borders'=>[
							'allborders' => [
								'style' => PHPExcel_Style_Border::BORDER_THIN,
							]
						],
					]
				);
		return true;
	}

	/**
	 * Возвращает стиль для оформления ячейки взависимости от текущего уровня
	 * @return array массив описания формата ячейки в зависимости от уровня
	 */
	public function getLevelStyle(){

		$levelStyles = [
			[
				'fill' => 	[
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'startcolor' => [
									'argb' => 'FFF0F0F0',
								],
							],  
				'font'=>[
					'size'=> 8,
					'italic'=> true
				],
			],
			[
				'fill' => 	[
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'startcolor' => [
									'argb' => 'FFB2B2B2',
								],
							],  
				'font'=>[
					'size'=> 10,
					'bold'=> true
				],
			],
			[
				'fill' => 	[
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'startcolor' => [
									'argb' => 'FFC2C2C2',
								],
							],  
				'font'=>[
					'size'=> 9,
					'bold'=> true
				],
			],
			[
				'fill' => 	[
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'startcolor' => [
									'argb' => 'FFD2D2D2',
								],
							],  
				'font'=>[
					'size'=> 9,
					'bold'=> true
				],
			],
			[
				'fill' => 	[
								'type' => PHPExcel_Style_Fill::FILL_SOLID,
								'startcolor' => [
									'argb' => 'FFE2E2E2',
								],
							],  
				'font'=>[
					'size'=> 9,
					'bold'=> true
				],
			]
		];
		return $levelStyles[$this->activeLevel] ?: $levelStyles[0];
	}

	/**
	 * Добавляет отстыпы в начало строки
	 * @param string $str исходная строка
	 * @return string     Последняя ячейка
	 */
	private function addTabs($str){
		return str_pad($str, $this->activeLevel * 3 + strlen($str), " ", STR_PAD_LEFT); //Эмилурую типа таб для отступа подкатегорий
	}

	/**
	 * Послений активный ячейка, понял да
	 * @param  string $dimention размеры возвращаемые функцией калькулирования
	 * @return string            Последняя ячейка
	 */
	private function getLastCell($dimention){
		return explode(':',$dimention)[1];
	}

	/**
	 * Размер активной области номерком
	 * @param  string $dimention последняя ячейка
	 * @return int               Высота активного листа в ячейках
	 */
	private function getSheetHeight($dimention){
		preg_match('/[\d]+/', $this->getLastCell($dimention), $match);
		return (int)$match[0];
	}

	
} 
?>