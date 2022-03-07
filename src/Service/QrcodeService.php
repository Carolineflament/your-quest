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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class QrcodeService
{
    private $routerInterface;

    public function __construct(RouterInterface $routerInterface)
    {
        $this->routerInterface = $routerInterface;
    }

    public function qrcode(Checkpoint $checkpoint)
    {
        $writer = new PngWriter();

        // Create QR code
        $url = $this->routerInterface->generate('front_checkpoint_check', ['id' => $checkpoint->getId(), 'token' => sha1($checkpoint->getTitle())], UrlGeneratorInterface::ABSOLUTE_URL);

        $qrCode = QR::create('URL:'.$url)
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
