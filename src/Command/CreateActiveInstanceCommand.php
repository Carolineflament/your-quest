<?php

namespace App\Command;

use App\Entity\Checkpoint;
use App\Entity\Game;
use App\Entity\Instance;
use App\Entity\Round;
use App\Entity\ScanQR;
use App\Entity\User;
use App\Repository\CheckpointRepository;
use App\Repository\GameRepository;
use App\Repository\RoleRepository;
use App\Service\MySlugger;
use App\Service\QrcodeService;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateActiveInstanceCommand extends Command
{
    protected static $defaultName = 'app:instance:now';
    protected static $defaultDescription = 'Create a test game and its four hour active instance now, with all their related entities datas';

    // Pour intéragir avec les entités et services
    private $roleRepository;
    private $entityManager;
    private $passwordHasher;
    private $slugger;
    private $qrcodeService;
    private $gameRepository;
    private $checkpointRepository;
    

    public function __construct(RoleRepository $roleRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, MySlugger $slugger, QrcodeService $qrcodeService, GameRepository $gameRepository, CheckpointRepository $checkpointRepository)
    {
        $this->roleRepository = $roleRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        $this->slugger = $slugger;
        $this->qrcodeService = $qrcodeService;
        $this->gameRepository = $gameRepository;
        $this->checkpointRepository = $checkpointRepository;
        
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            // Add 4 required command arguments
            ->addArgument('gameName', InputArgument::REQUIRED, 'What is the name of the new game ?')
            ->addArgument('numberOfCheckpoints', InputArgument::REQUIRED, 'How many checkpoints ?')
            ->addArgument('instanceName', InputArgument::REQUIRED, 'What is the name of the new instance ?')
            ->addArgument('numberOfPlayers', InputArgument::REQUIRED, 'How many players are playing this instance ?')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Get all the command arguments
        $gameNameArg = $input->getArgument('gameName');
        $numberOfCheckpointsArg = $input->getArgument('numberOfCheckpoints');
        $instanceNameArg = $input->getArgument('instanceName');
        $numberOfPlayersArg = $input->getArgument('numberOfPlayers');

        // Generate game slug to add it on users credentials
        $gameSlug = $this->slugger->slugify($gameNameArg);

        // Class pour styliser les inputs/outputs
        $io = new SymfonyStyle($input, $output);

        $io->info("Lancement de la commande de création d'un jeu et de son instance valide depuis 1h et pendant encore 4h :");

        /********** USERS **********/

        $io->info("Création de l'organisateur");

        // Get necessary Role objects 
        $organisatorRole = $this->roleRepository->findOneBy(['slug' => 'ROLE_ORGANISATEUR']);
        $playerRole = $this->roleRepository->findOneBy(['slug' => 'ROLE_JOUEUR']);

        
        // One new organisator
        $organisator = new User();
        $organisator->setEmail('orga'.$gameSlug.'@organisateur.com');
        $organisator->setRole($organisatorRole);
        $organisator->setPassword($this->passwordHasher->hashPassword($organisator, 'organisateur'));
        $organisator->setUsername('orga'.$gameSlug);
        $organisator->setLastname('Numéro1');
        $organisator->setFirstname('Orga');
        $organisator->setStatus(true);

        $this->entityManager->persist($organisator);

        // Ten new players

        $io->info("Création de $numberOfPlayersArg joueurs");

        // Players array for next uses
        $playerObjectsArray = [];

        for ($i = 1; $i <= $numberOfPlayersArg; $i++) {
            $player = new User();
            $player->setEmail('joueur'.$i.$gameSlug.'@joueur.com');
            $player->setRole($playerRole);
            $player->setPassword($this->passwordHasher->hashPassword($player, 'joueur'));
            $player->setUsername('joueur'.$i.$gameSlug);
            $player->setLastname('Numéro'.$i);
            $player->setFirstname('Joueur');
            $player->setStatus(true);

            $playerObjectsArray[] = $player;

            $this->entityManager->persist($player);
        }
        
        /********** GAME **********/

        $io->info("Création du jeu $gameNameArg");

        $game = new Game();
        $game->setTitle($gameNameArg);
        $game->setSummary("Ceci est un jeu créé par la commande bin/console app:instance:now $gameNameArg $numberOfCheckpointsArg $instanceNameArg $numberOfPlayersArg");
        $game->setAddress('Adresse de test');
        $game->setPostalCode('66666');
        $game->setCity('Testville');
        $game->setUser($organisator);

        $this->entityManager->persist($game);

        /********** CHECKPOINTS **********/

        $io->info("Création de $numberOfCheckpointsArg checkpoints");

        // Checkpoints array for next uses
        $checkpointObjectsArray = [];

        for ($i = 1; $i <= $numberOfCheckpointsArg; $i++) {
            $checkpoint = new Checkpoint();
            $checkpoint->setTitle("Checkpoint de test (initialement $i/$numberOfCheckpointsArg)");
            $checkpoint->setOrderCheckpoint($i);
            $checkpoint->setSuccessMessage("Bravo ! Rendez-vous maintenant au checkpoint suivant.");
            $checkpoint->setGame($game);

            $checkpointObjectsArray[] = $checkpoint;

            $this->entityManager->persist($checkpoint);

        }

        /********** INSTANCE **********/

        $io->info("Création de l'instance $instanceNameArg");

        $instance = new Instance();
        $instance->setTitle($instanceNameArg);
        $instance->setMessage("Ceci est une instance créée par la commande bin/console app:instance:now $gameNameArg $numberOfCheckpointsArg $instanceNameArg $numberOfPlayersArg");
        // get actual date
        $now = new DateTimeImmutable();
        // substract 1h to set the start hour of the instance
        $InstanceStart = $now->sub(new DateInterval('PT1H'));
        $instance->setStartAt($InstanceStart);
        // add 4h to set the end hour of the instance
        $InstanceEnd = $now->add(new DateInterval('PT4H'));
        $instance->setEndAt($InstanceEnd);
        $instance->setGame($game);
        
        $this->entityManager->persist($instance);

        /********** ROUND **********/

        $io->info("Création des rounds (1 round par joueur pour cette instance)");

        // Rounds array for next uses
        $roundObjectsArray = [];

        // 1 round for each
        foreach ($playerObjectsArray as $currentPlayer) {
            $round = new Round();
            // Round startAt = Instance startAt
            $round->setStartAt($InstanceStart);
            // No endAt, it will be set by the last checkpoint scan...
            $round->setInstance($instance);
            $round->setUser($currentPlayer);

            $roundObjectsArray[] = $round;

            $this->entityManager->persist($round);
        }

        /********** SCAN QR **********/

        $io->info("Création d'un nombre aléatoire de scans de QR codes pour chaque round, dans l'ordre des checkpoints du jeu.");

        // Number of checkpoints of this Game
        $numberOfCheckpoints = count($checkpointObjectsArray);

        // For each round we generate many scans
        foreach ($roundObjectsArray as $currentRound) {

            // Random number of QR code scans (= how many checkpoints have been scanned ?)
            $randomQRScanNumber = rand(1, $numberOfCheckpoints); 
            // Var to calculate the next checkpoint scan datetime
            $previousScanDate = $InstanceStart;

            for ($i = 1; $i <= $randomQRScanNumber; $i++) {
                $scanQR = new ScanQR();
                // If first checkpoint : scan is the Round starAt (=instance startAt)
                if ($i == 1) {
                    $scanQR->setScanAt($InstanceStart);
                // Else we add 10 min between each scan
                } else {
                    $scanAt = $previousScanDate->add(new DateInterval('PT10M'));
                    $scanQR->setScanAt($scanAt);
                    // Increment for next loop
                    $previousScanDate = $scanAt;
                }
                // If last checkpoint : scan is the Round endAt
                if ($i == $numberOfCheckpoints) {
                    $currentRound->setEndAt($scanAt);
                }
                $scanQR->setCheckpoint($checkpointObjectsArray[$i-1]);
                $scanQR->setRound($currentRound);

                $this->entityManager->persist($scanQR);
            }
        }

        $this->entityManager->flush();
        

        /********** QR codes PNG generation **********/

        $io->info("Génération des images PNG des QR codes des checkpoints");

        // Get just created Game
        $createdGame = $this->gameRepository->findOneBy(['slug' => $gameSlug]);
        $createdGameId = $createdGame->getId();

        // Get the checkpoints list of the Game, after flush because we need Id property
        $gameCheckpointsList = $this->checkpointRepository->findBy(['game' => $createdGameId]);
        
        // Create a QR code PNG file for each checkpoint
        foreach ($gameCheckpointsList as $checkpoint) {

            $this->qrcodeService->qrcode($checkpoint);
        }
        
        return Command::SUCCESS;
    }
    
}