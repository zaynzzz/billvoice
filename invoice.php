<?php
require_once('includes/autoload.php');

class invoicr extends FPDF_rotation

{
	var $font = 'helvetica';
    var $columnOpacity = 0.06;
    var $columnSpacing = 0.3;
    var $referenceformat = array('.', ',');
    var $margins = array('l' => 20, 't' => 20, 'r' => 20);
    var $l;
    var $document;
    var $type;
    var $reference;
    var $logo;
    var $color;
    var $date;
    var $due;
    var $from;
	public $currency = 'Rp';  // Definisikan di dalam class
    var $to;
    var $ship; // ADDED SHIPPING
    var $items;
    var $totals;
    var $badge;
    var $addText;
    var $footernote;
    var $dimensions;
 
	public function Body() {
		// Tambahkan teks atau elemen ke dalam halaman PDF
		$this->SetFont('Arial', 'B', 12);
		$this->Cell(40, 10, 'Invoice Details');
		$this->Ln();
		// Tambahkan lebih banyak konten sesuai kebutuhan
	}
	
	public function index($size = 'A4', $currency = 'Rp', $language = 'en')
	{
		// Initialize the class properties
		$this->columns = 5;
		$this->items = array();
		$this->totals = array();
		$this->addText = array();
		$this->firstColumnWidth = 70;
		$this->currency = $currency;
		$this->maxImageDimensions = array(230, 130);

		// Set language and document size
		$this->setLanguage($language);
		$this->setDocumentSize($size);
		$this->setColor("#222222");

		// Call FPDF_rotation constructor
		parent::__construct('P', 'mm', $size); // Properly calling parent constructor

		// Set up the PDF margins and other configurations
		$this->AliasNbPages();
		$this->SetMargins($this->margins['l'], $this->margins['t'], $this->margins['r']);
	}
	

    public function setType($title)
    {
        $this->title = $title;
    }

    public function setColor($rgbcolor)
    {
        $this->color = $this->hex2rgb($rgbcolor);
    }

    public function setDate($date)
    {
        $this->date = $date;
    }

    public function setDue($date)
    {
        $this->due = $date;
    }

    public function setLogo($logo = 0, $maxWidth = 0, $maxHeight = 0)
    {
        if ($maxWidth && $maxHeight) {
            $this->maxImageDimensions = array($maxWidth, $maxHeight);
        }
        $this->logo = $logo;
        $this->dimensions = $this->resizeToFit($logo);
    }

    public function setFrom($data)
    {
        $this->from = array_filter($data);
    }

    public function setTo($data)
    {
        $this->to = $data;
    }

    public function shipTo($data)
    {
        $this->ship = $data;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setNumberFormat($decimals, $thousands_sep)
    {
        $this->referenceformat = array($decimals, $thousands_sep);
    }

    public function flipflop()
    {
        $this->flipflop = true;
    }

    public function addItem($item, $description, $quantity, $vat, $price, $discount, $total)
    {
        $p['item'] = $item;
        $p['description'] = $this->br2nl($description);
        $p['vat'] = $vat;

        if (is_numeric($vat)) {
            $p['vat'] = $this->currency . ' ' . number_format($vat, 2, $this->referenceformat[0], $this->referenceformat[1]);
        }

        $p['quantity'] = $quantity;
        $p['price'] = $price;
        $p['total'] = $total;

        if ($discount !== false) {
            $this->firstColumnWidth = 58;
            $p['discount'] = $discount;

            if (is_numeric($discount)) {
                $p['discount'] = $this->currency . ' ' . number_format($discount, 2, $this->referenceformat[0], $this->referenceformat[1]);
            }

            $this->discountField = true;
            $this->columns = 6;
        }

        $this->items[] = $p;
    }

    public function addTotal($name, $value, $colored = 0)
    {
        $t['name'] = $name;
        $t['value'] = $value;
        if (is_numeric($value)) {
            $t['value'] = $this->currency . ' ' . number_format($value, 2, $this->referenceformat[0], $this->referenceformat[1]);
        }
        $t['colored'] = $colored;
        $this->totals[] = $t;
    }

    public function addTitle($title)
    {
        $this->addText[] = array('title', $title);
    }

    public function addParagraph($paragraph)
    {
        $paragraph = $this->br2nl($paragraph);
        $this->addText[] = array('paragraph', $paragraph);
    }

    public function addBadge($badge)
    {
        $this->badge = $badge;
    }

    public function setFooternote($note)
    {
        $this->footernote = $note;
    }

    public function render($name = '', $destination = '')
    {
        $this->AddPage();
        $this->Body();
        $this->AliasNbPages();
        $this->Output($name, $destination);
    }

    private function setLanguage($language)
    {
        $this->language = $language;
        include('languages/' . $language . '.inc');
        $this->l = $l;  // Assuming $l is defined in the included file
    }

    private function setDocumentSize($dsize)
    {
        switch ($dsize) {
            case 'A4':
                $this->document = array('w' => 210, 'h' => 297);
                break;
            case 'letter':
                $this->document = array('w' => 215.9, 'h' => 279.4);
                break;
            case 'legal':
                $this->document = array('w' => 215.9, 'h' => 355.6);
                break;
            default:
                $this->document = array('w' => 210, 'h' => 297);
                break;
        }
    }

    private function resizeToFit($image)
    {
        list($width, $height) = getimagesize($image);
        $newWidth = $this->maxImageDimensions[0] / $width;
        $newHeight = $this->maxImageDimensions[1] / $height;
        $scale = min($newWidth, $newHeight);
        return array(
            round($this->pixelsToMM($scale * $width)),
            round($this->pixelsToMM($scale * $height))
        );
    }

    private function pixelsToMM($val)
    {
        $mm_inch = 25.4;
        $dpi = 96;
        return $val * $mm_inch / $dpi;
    }

    private function hex2rgb($hex)
    {
        $hex = str_replace("#", "", $hex);

        if (strlen($hex) == 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return array($r, $g, $b);
    }

    private function br2nl($string)
    {
        return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
    }
}
?>
