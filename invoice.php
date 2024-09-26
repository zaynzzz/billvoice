<?php
require_once('includes/autoload.php');

/*******************************************************************************
* Invoicr                                                                      *
*                                                                              *
* Version: 1.1.1	                                                               *
* Author:  EpicBrands BVBA                                    				   *
* http://www.epicbrands.be                                                     *
*******************************************************************************/
class invoicr extends FPDF_rotation 
{

	var $font = 'helvetica';
	var $columnOpacity = 0.06;
	var $columnSpacing = 0.3;
	var $referenceformat = array('.',',');
	var $margins = array('l'=>20,'t'=>20,'r'=>20);
    public $currency;  // Deklarasi properti currency
    public $maxImageDimensions; // Declare this property
    public $title;              // Declare this property
    public $firstColumnWidth;    // Declare this property
    public $discountField;       // Declare this property
    public $columns;             // Declare this property
    public $productsEnded;       // Declare this property

	var $l;
	var $document;
	var $type;
	var $reference;
	var $logo;
	var $color;
	var $date;
	var $due;
	var $from;
	var $to;
	var $ship; // ADDED SHIPPING
	var $items;
	var $totals;
	var $badge;
	var $addText;
	var $footernote;
	var $dimensions;
	
	/*******************************************************************************
	*                                                                              *
	*                               Public methods                                 *
	*                                                                              *
	*******************************************************************************/
	function invoicr($size='A4',$currency='€',$language='en')
	{
		$this->columns = 5;
		$this->items = array();
		$this->totals = array();
		$this->addText = array();
		$this->firstColumnWidth = 70;
		$this->currency = $currency;
		$this->maxImageDimensions = array(230,130);
        $this->discountField = false;
        $this->productsEnded = false;
		$this->setLanguage($language);
		$this->setDocumentSize($size);
		$this->setColor("#222222");
		
		$this->FPDF('P','mm',array($this->document['w'],$this->document['h']));
		$this->AliasNbPages();
		$this->SetMargins($this->margins['l'],$this->margins['t'],$this->margins['r']);
	}
	
	function setType($title)
	{
		$this->title = $title;
	}
	
	function setColor($rgbcolor)
	{
		$this->color = $this->hex2rgb($rgbcolor);
	}
	
	function setDate($date)
	{
		$this->date = $date;
	}
	
	function setDue($date)
	{
		$this->due = $date;
	}
	
	function setLogo($logo=0,$maxWidth=0,$maxHeight=0)
	{
		if($maxWidth and $maxHeight) {
			$this->maxImageDimensions = array($maxWidth,$maxHeight);
		}
		$this->logo = $logo;
		$this->dimensions = $this->resizeToFit($logo);
	}
	
	function setFrom($data)
	{
		$this->from = array_filter($data);
        //print_r(array_filter($data));
	}
	
	function setTo($data)
	{
		$this->to = $data;
	}

	function shipTo($data)
	{
		$this->ship = $data;
	}
	
	function setReference($reference)
	{
		$this->reference = $reference;
	}
	
	function setNumberFormat($decimals,$thousands_sep)
	{
		$this->referenceformat = array($decimals,$thousands_sep);
	}
	
	function flipflop()
	{
		$this->flipflop = true;
	}
	
	function addItem($item,$description,$quantity,$vat,$price,$discount,$total)
	{
		$p['item'] 			= $item;
		$p['description'] 	= $this->br2nl($description);
		$p['vat']			= $vat;
		if(is_numeric($vat)) {
			$p['vat']		= $this->currency.' '.number_format($vat,2,$this->referenceformat[0],$this->referenceformat[1]);
		} 
		$p['quantity'] 		= $quantity;
		$p['price']			= $price;
		$p['total']			= $total;
		
		if($discount!==false) {
			$this->firstColumnWidth = 58;
			$p['discount'] = $discount;
			if(is_numeric($discount)) {
				$p['discount']	= $this->currency.' '.number_format($discount,2,$this->referenceformat[0],$this->referenceformat[1]);
			}
			$this->discountField = true;
			$this->columns = 6;
		}
		
		$this->items[]		= $p;
	}
	
	function addTotal($name,$value,$colored=0)
	{
		$t['name']			= $name;
		$t['value']			= $value;
		if(is_numeric($value)) {
			$t['value']			= $this->currency.' '.number_format($value,2,$this->referenceformat[0],$this->referenceformat[1]);
		} 
		$t['colored']		= $colored;
		$this->totals[]		= $t;
	}
	
	function addTitle($title) 
	{
		$this->addText[] = array('title',$title);
	}
	
	function addParagraph($paragraph) 
	{
		$paragraph = $this->br2nl($paragraph);
		$this->addText[] = array('paragraph',$paragraph);
	}
	
	function addBadge($badge)
	{
		$this->badge = $badge;
	}
	
	function setFooternote($note) 
	{
		$this->footernote = $note;
	}
	
	function render($name='',$destination='')
	{
		$this->AddPage();
		$this->Body();
		$this->AliasNbPages();
		$this->Output($name,$destination);
	}
	
	/*******************************************************************************
	*                                                                              *
	*                               Create Invoice                                 *
	*                                                                              *
	*******************************************************************************/
	public function Header()
{
    // Check and initialize necessary elements if not already set
    $this->document['w'] = $this->document['w'] ?? $this->w; // Default width
    $this->margins = $this->margins ?? ['l' => 10, 'r' => 10, 't' => 10]; // Default margins
    $this->l = $this->l ?? [
        'number' => 'Number', 
        'date' => 'Date', 
        'due' => 'Due', 
        'from' => 'From',
        'to' => 'To',
        'ship' => 'Ship',
        'product' => 'Product',
        'amount' => 'Amount',
        'vat' => 'VAT',
        'price' => 'Price',
        'discount' => 'Discount',
        'total' => 'Total'
    ]; // Default labels

    if (isset($this->logo)) {
        // Adjust the y-coordinate to move the logo higher
		$this->Image($this->logo, 10, $this->margins['t'] - 15, 40, 40); // Move x to 10, y remains at the current position
    }

    // Title
    $this->SetTextColor(0, 0, 0);
    $this->SetFont($this->font, 'B', 20);
    $title = strtoupper($this->title ?? 'Default Title');
    $this->Cell(0, 5, iconv("UTF-8", "ISO-8859-1", $title), 0, 1, 'R');
    $this->SetFont($this->font, '', 9);
    $this->Ln(5);

    $lineheight = 5;
    // Calculate position of strings
    $this->SetFont($this->font, 'B', 9);    
    $positionX = $this->document['w'] - $this->margins['l'] - $this->margins['r'] - max(
        $this->GetStringWidth(strtoupper($this->l['number'] ?? '')),
        $this->GetStringWidth(strtoupper($this->l['date'] ?? '')),
        $this->GetStringWidth(strtoupper($this->l['due'] ?? ''))
    ) - 35;

    // Number
    $this->Cell($positionX, $lineheight);
    $this->SetTextColor($this->color[0], $this->color[1], $this->color[2]);
    $numberLabel = strtoupper($this->l['number'] ?? 'number').':';
    $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", $numberLabel), 0, 0, 'L');
    $this->SetTextColor(50, 50, 50);
    $this->SetFont($this->font, '', 9);
    $this->Cell(0, $lineheight, $this->reference ?? 'Not set', 0, 1, 'R');
    
    // Date
    $this->Cell($positionX, $lineheight);
    $this->SetFont($this->font, 'B', 9);
    $dateLabel = strtoupper($this->l['date'] ?? 'date').':';
    $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", $dateLabel), 0, 0, 'L');   
    $this->SetTextColor(50, 50, 50);
    $this->SetFont($this->font, '', 9);
    $this->Cell(0, $lineheight, $this->date ?? 'Not set', 0, 1, 'R');

    // Due date
    if ($this->due) {
        $this->Cell($positionX, $lineheight);
        $this->SetFont($this->font, 'B', 9);
        $dueLabel = strtoupper($this->l['due'] ?? 'due').':';
        $this->Cell(32, $lineheight, iconv("UTF-8", "ISO-8859-1", $dueLabel), 0, 0, 'L');    
        $this->SetTextColor(50, 50, 50);
        $this->SetFont($this->font, '', 9);
        $this->Cell(0, $lineheight, $this->due ?? 'Not set', 0, 1, 'R');
    }

    // Additional implementation for the first page, shipping details, and table headers remains similar but should also include similar checks and error handling as demonstrated above.
}

	function Body()
	{	
		$width_other = ($this->document['w']-$this->margins['l']-$this->margins['r']-$this->firstColumnWidth-($this->columns*$this->columnSpacing))/($this->columns-1);
		$cellHeight = 9;
		$bgcolor = (1-$this->columnOpacity)*255;
		if($this->items) {
			foreach($this->items as $item) 
			{
				if($item['description']) 
				{
					//Precalculate height
					$calculateHeight = new invoicr;
					$calculateHeight->addPage();
					$calculateHeight->setXY(0,0);
					$calculateHeight->SetFont($this->font,'',7);	
					$calculateHeight->MultiCell($this->firstColumnWidth,3,iconv("UTF-8", "ISO-8859-1",$item['description']),0,'L',1);
					$descriptionHeight = $calculateHeight->getY()+$cellHeight+2;
					$pageHeight = $this->document['h']-$this->GetY()-$this->margins['t']-$this->margins['t'];
					if($pageHeight<0) 
					{
						$this->AddPage();
					}
				}
				$cHeight = $cellHeight;
				$this->SetFont($this->font,'b',8);
				$this->SetTextColor(50,50,50);
				$this->SetFillColor($bgcolor,$bgcolor,$bgcolor);
				$this->Cell(1,$cHeight,'',0,0,'L',1);
				$x = $this->GetX();
				$this->Cell($this->firstColumnWidth,$cHeight,iconv("UTF-8", "ISO-8859-1",$item['item']),0,0,'L',1);
				if($item['description'])
				{
					$resetX = $this->GetX();
					$resetY = $this->GetY();
					$this->SetTextColor(120,120,120);
					$this->SetXY($x,$this->GetY()+8);
					$this->SetFont($this->font,'',7);			
					$this->MultiCell($this->firstColumnWidth,3,iconv("UTF-8", "ISO-8859-1",$item['description']),0,'L',1);
					//Calculate Height
					$newY = $this->GetY();
					$cHeight = $newY-$resetY+2;
					//Make our spacer cell the same height
					$this->SetXY($x-1,$resetY);
					$this->Cell(1,$cHeight,'',0,0,'L',1);
					//Draw empty cell
					$this->SetXY($x,$newY);
					$this->Cell($this->firstColumnWidth,2,'',0,0,'L',1);
					$this->SetXY($resetX,$resetY);	
				}
				$this->SetTextColor(50,50,50);
				$this->SetFont($this->font,'',8);
				$this->Cell($this->columnSpacing,$cHeight,'',0,0,'L',0);
				$this->Cell($width_other,$cHeight,$item['quantity'],0,0,'C',1);
				$this->Cell($this->columnSpacing,$cHeight,'',0,0,'L',0);
				$this->Cell($width_other,$cHeight,iconv('UTF-8', 'windows-1252', $item['vat']),0,0,'C',1);
				$this->Cell($this->columnSpacing,$cHeight,'',0,0,'L',0);
				$this->Cell($width_other,$cHeight,iconv('UTF-8', 'windows-1252', $this->currency.' '.number_format($item['price'],2,$this->referenceformat[0],$this->referenceformat[1])),0,0,'C',1);
				if(isset($this->discountField)) 
				{
					$this->Cell($this->columnSpacing,$cHeight,'',0,0,'L',0);
					if(isset($item['discount'])) 
					{
						$this->Cell($width_other,$cHeight,iconv('UTF-8', 'windows-1252',$item['discount']),0,0,'C',1);
					} 
					else 
					{
						$this->Cell($width_other,$cHeight,'',0,0,'C',1);
					}
				}
				$this->Cell($this->columnSpacing,$cHeight,'',0,0,'L',0);
				$this->Cell($width_other,$cHeight,iconv('UTF-8', 'windows-1252', $this->currency.' '.number_format($item['total'],2,$this->referenceformat[0],$this->referenceformat[1])),0,0,'C',1);
				$this->Ln();
				$this->Ln($this->columnSpacing);
			}
		}
		$badgeX = $this->getX();
		$badgeY = $this->getY();
		
		//Add totals
		if($this->totals) 
		{
			foreach($this->totals as $total) 
			{
				$this->SetTextColor(50,50,50);
				$this->SetFillColor($bgcolor,$bgcolor,$bgcolor);
				$this->Cell(1+$this->firstColumnWidth,$cellHeight,'',0,0,'L',0);
				for($i=0;$i<$this->columns-3;$i++) 
				{
					$this->Cell($width_other,$cellHeight,'',0,0,'L',0);
					$this->Cell($this->columnSpacing,$cellHeight,'',0,0,'L',0);
				}
				$this->Cell($this->columnSpacing,$cellHeight,'',0,0,'L',0);
				if($total['colored']) 
				{
					$this->SetTextColor(255,255,255);
					$this->SetFillColor($this->color[0],$this->color[1],$this->color[2]);
				}
				$this->SetFont($this->font,'b',8);
				$this->Cell(1,$cellHeight,'',0,0,'L',1);
				$this->Cell($width_other-1,$cellHeight,iconv('UTF-8', 'windows-1252',$total['name']),0,0,'L',1);
				$this->Cell($this->columnSpacing,$cellHeight,'',0,0,'L',0);
				$this->SetFont($this->font,'b',8);
				$this->SetFillColor($bgcolor,$bgcolor,$bgcolor);
				if($total['colored']) 
				{
					$this->SetTextColor(255,255,255);
					$this->SetFillColor($this->color[0],$this->color[1],$this->color[2]);
				}
				$this->Cell($width_other,$cellHeight,iconv('UTF-8', 'windows-1252',$total['value']),0,0,'C',1);
				$this->Ln();
				$this->Ln($this->columnSpacing);
			}
		}
		$this->productsEnded = true;
		$this->Ln();
		$this->Ln(3);
		
		
		//Badge
		if($this->badge) 
		{
			$badge = ' '.strtoupper($this->badge).' ';
			$resetX = $this->getX();
			$resetY = $this->getY();
			$this->setXY($badgeX,$badgeY+15);
			$this->SetLineWidth(0.4);
			$this->SetDrawColor($this->color[0],$this->color[1],$this->color[2]);		
			$this->setTextColor($this->color[0],$this->color[1],$this->color[2]);
			$this->SetFont($this->font,'b',15);
			$this->Rotate(10,$this->getX(),$this->getY());
			$this->Rect($this->GetX(),$this->GetY(),$this->GetStringWidth($badge)+2,10);
			$this->Write(10,$badge);
			$this->Rotate(0);
			if($resetY>$this->getY()+20) 
			{
				$this->setXY($resetX,$resetY);
			} 
			else 
			{
				$this->Ln(18);
			}
		}
		
		//Add information
		foreach($this->addText as $text) 
		{
			if($text[0] == 'title') 
			{
				$this->SetFont($this->font,'b',9);
				$this->SetTextColor(50,50,50);
				$this->Cell(0,10,iconv("UTF-8", "ISO-8859-1",strtoupper($text[1])),0,0,'L',0);
				$this->Ln();
				$this->SetLineWidth(0.3);
				$this->SetDrawColor($this->color[0],$this->color[1],$this->color[2]);
				$this->Line($this->margins['l'], $this->GetY(),$this->document['w']-$this->margins['r'], $this->GetY());
				$this->Ln(4);
			}
			if($text[0] == 'paragraph') 
			{
				$this->SetTextColor(80,80,80);
				$this->SetFont($this->font,'',8);
				$this->MultiCell(0,4,iconv("UTF-8", "ISO-8859-1",$text[1]),0,'L',0);
				$this->Ln(4);
			}
		}
	}

	public function Footer()
{
    // Set position of the footer
    $this->SetY(-$this->margins['t']);
    
    // Set font and text color for footer
    $this->SetFont($this->font, '', 8);
    $this->SetTextColor(50, 50, 50);

    // Display footernote if set
    $this->Cell(0, 10, $this->footernote ?? '', 0, 0, 'L');
    
    // Display page number
    $pageText = ($this->l['page'] ?? 'Page') . ' ' . $this->PageNo() . ' ' . ($this->l['page_of'] ?? 'of') . ' {nb}';
    $this->Cell(0, 10, $pageText, 0, 0, 'R');
}

	/*******************************************************************************
	*                                                                              *
	*                               Private methods                                *
	*                                                                              *
	*******************************************************************************/
	private function setLanguage($language)
	{
		$this->language = $language;
		include('languages/'.$language.'.inc');
		$this->l = $l;
	}
	
	private function setDocumentSize($dsize)
	{
		switch ($dsize)
		{
			case 'A4':
				$document['w'] = 210;
				$document['h'] = 297;
				break;
			case 'letter':
				$document['w'] = 215.9;
				$document['h'] = 279.4;
				break;
			case 'legal':
				$document['w'] = 215.9;
				$document['h'] = 355.6;
				break;
			default:
				$document['w'] = 210;
				$document['h'] = 297;
				break;
		}
		$this->document = $document;
	}
	
	private function resizeToFit($image)
	{
		// Check if the file exists before attempting to get the size
		if (!file_exists($image)) {
			// Handle error, return a default size or throw an exception
			return array($this->maxImageDimensions[0], $this->maxImageDimensions[1]);
		}
	
		// Get image dimensions
		list($width, $height) = getimagesize($image);
	
		if ($width === null || $height === null) {
			// Handle error if width or height is null, return a default size
			return array($this->maxImageDimensions[0], $this->maxImageDimensions[1]);
		}
	
		// Check if maxImageDimensions contains 'auto'
		if ($this->maxImageDimensions[0] === 'auto') {
			$newWidth = $this->pixelsToMM($width);
		} else {
			$newWidth = $this->maxImageDimensions[0] / $width;
		}
	
		if ($this->maxImageDimensions[1] === 'auto') {
			$newHeight = $this->pixelsToMM($height);
		} else {
			$newHeight = $this->maxImageDimensions[1] / $height;
		}
	
		// Calculate the scaling factor only if both dimensions are numbers
		if (is_numeric($newWidth) && is_numeric($newHeight)) {
			$scale = min($newWidth, $newHeight);
			return array(
				round($this->pixelsToMM($scale * $width)),
				round($this->pixelsToMM($scale * $height))
			);
		}
	
		// Default return if something went wrong
		return array($this->maxImageDimensions[0], $this->maxImageDimensions[1]);
	}
	
	private function pixelsToMM($val) 
	{
		$mm_inch = 25.4;
		$dpi = 96;
		return $val * $mm_inch/$dpi;
	}
	
	private function hex2rgb($hex)
	{
	   $hex = str_replace("#", "", $hex);
	
	   if(strlen($hex) == 3) {
	      $r = hexdec(substr($hex,0,1).substr($hex,0,1));
	      $g = hexdec(substr($hex,1,1).substr($hex,1,1));
	      $b = hexdec(substr($hex,2,1).substr($hex,2,1));
	   } else {
	      $r = hexdec(substr($hex,0,2));
	      $g = hexdec(substr($hex,2,2));
	      $b = hexdec(substr($hex,4,2));
	   }
	   $rgb = array($r, $g, $b);
	   return $rgb;
	}
	
	private function br2nl($string)
	{
    	return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
	}  

}

?>