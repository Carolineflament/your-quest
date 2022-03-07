<?php

namespace App\Command;

use App\Entity\Checkpoint;
use App\Entity\Game;
use App\Entity\Instance;
use App\Entity\Round;
use App\Entity\ScanQR;
use App\Entity\User;
use App\Repository\RoleRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class CreateActiveInstanceCommand extends Command
{
    protected static $defaultName = 'app:instance:now';
    protected static $defaultDescription = 'Create a test game and its one hour active instance now, with all their related entities datas';

    // Pour intéragir avec les entités
    private $roleRepository;
    private $entityManager;
    private $passwordHasher;

    

    public function __construct(RoleRepository $roleRepository, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
    {
        $this->roleRepository = $roleRepository;
        $this->entityManager = $entityManager;
        $this->passwordHasher = $passwordHasher;
        

        parent::__construct();
    }

    protected function configure(): void
    {
        
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Class pour styliser les inputs/outputs
        $io = new SymfonyStyle($input, $output);

        $io->info("Création d'une instance de test, valide depuis 1h et pendant encore 1h.");

        /********** USERS **********/

        // Get necessary Role objects 
        $organisatorRole = $this->roleRepository->findOneBy(['slug' => 'ROLE_ORGANISATEUR']);
        $playerRole = $this->roleRepository->findOneBy(['slug' => 'ROLE_JOUEUR']);

        
        // One new organisator
        $organisator = new User();
        $organisator->setEmail('organisateur1@organisateur.com');
        $organisator->setRole($organisatorRole);
        $organisator->setPassword($this->passwordHasher->hashPassword($organisator, 'organisateur1'));
        $organisator->setUsername('organisateur1');
        $organisator->setLastname('Numéro1');
        $organisator->setFirstname('Orga');
        $organisator->setStatus(true);

        $this->entityManager->persist($organisator);

        // Ten new players

        // Players array for next uses
        $playerObjectsArray = [];

        for ($i = 1; $i <= 10; $i++) {
            $player = new User();
            $player->setEmail("joueur$i@joueur.com");
            $player->setRole($playerRole);
            $player->setPassword($this->passwordHasher->hashPassword($player, "joueur$i"));
            $player->setUsername("joueur$i");
            $player->setLastname("Numéro$i");
            $player->setFirstname('Joueur');
            $player->setStatus(true);

            $playerObjectsArray[] = $player;

            $this->entityManager->persist($player);
        }
        
        /********** GAME **********/

        $game = new Game();
        $game->setTitle('Jeu de test créé par une commande');
        $game->setAddress('Adresse de test');
        $game->setPostalCode('66666');
        $game->setCity('Testville');
        $game->setUser($organisator);

        $this->entityManager->persist($game);

        /********** CHECKPOINTS **********/

        // Checkpoints array for next uses
        $checkpointObjectsArray = [];

        for ($i = 1; $i <= 5; $i++) {
            $checkpoint = new Checkpoint();
            $checkpoint->setTitle("Checkpoint de test créé par une commande ($i/5)");
            $checkpoint->setOrderCheckpoint($i);
            $checkpoint->setSuccessMessage("Bravo ! Rendez-vous maintenant au checkpoint suivant.");
            $checkpoint->setGame($game);

            $checkpointObjectsArray[] = $checkpoint;

            $this->entityManager->persist($checkpoint);
        }

        /********** INSTANCE **********/

        $instance = new Instance();
        $instance->setTitle('Instance de test créée par une commande');
        // get actual date
        $now = new DateTimeImmutable();
        // substract 1h to set the start hour of the instance
        $InstanceStart = $now->sub(new DateInterval('PT1H'));
        $instance->setStartAt($InstanceStart);
        // add 1h to set the end hour of the instance
        $InstanceEnd = $now->add(new DateInterval('PT1H'));
        $instance->setEndAt($InstanceEnd);
        $instance->setGame($game);
        
        $this->entityManager->persist($instance);

        /********** ROUND **********/

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
        
        return Command::SUCCESS;
    }
    
}