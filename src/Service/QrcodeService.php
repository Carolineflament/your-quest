<?php

namespace App\Service;

use App\Entity\Checkpoint;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Id;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelLow;
use Endroid\QrCode\QrCode as QR; // obligé d'utiliser un alias car le nom de cette class utilisée par le composant est identique au nom de mon entité !
use Endroid\QrCode\Label\Label;
use Endroid\QrCode\Logo\Logo;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;


class QrcodeService
{


    public function qrcode(Checkpoint $checkpoint)
    {
        $writer = new PngWriter();

        // Create QR code
        $qrCode = QR::create($checkpoint->getSuccessMessage())
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(new ErrorCorrectionLevelLow())
            ->setSize(300)
            ->setMargin(10)
            ->setRoundBlockSizeMode(new RoundBlockSizeModeMargin())
            ->setForegroundColor(new Color(0, 0, 0))
            ->setBackgroundColor(new Color(255, 255, 255));

        // Create generic logo
        // $logo = Logo::create(__DIR__.'/assets/symfony.png')
        //     ->setResizeToWidth(50);

        // Create generic label
        $label = Label::create('QR code du checkpoint n° ' . $checkpoint->getOrderCheckpoint())
            ->setTextColor(new Color(0, 0, 0));

        $result = $writer->write($qrCode, null, $label);

        // Save it to a file
        $result->saveToFile(__DIR__.'/../../public/assets/Qrcode/'.$checkpoint->getId(). 'qrcode.png');
    }
}
