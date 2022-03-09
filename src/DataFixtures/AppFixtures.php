<?php

namespace App\DataFixtures;

use App\DataFixtures\Provider\QuestProvider;
use App\Entity\Answer;
use App\Entity\Checkpoint;
use App\Entity\Enigma;
use App\Entity\Game;
use App\Entity\Instance;
use App\Entity\Role;
use App\Entity\Round;
use App\Entity\ScanQR;
use App\Entity\User;
use App\Service\MySlugger;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AppFixtures extends Fixture
{
    private $connection;
    private $passwordHasher;
    private $slugger;

    public function __construct(HttpClientInterface $client, UserPasswordHasherInterface $passwordHasher, MySlugger $slugger)
    {
        $this->passwordHasher = $passwordHasher;
        $this->client = $client;
        $this->slugger = $slugger;
    }

    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        $questProvider = new QuestProvider;

        /*****************ROLE ******************/

        $roleEntity = [];
        $roleName = [
            'Joueur', 'Organisateur', 'Admin'
        ];
        foreach ($roleName as $roles) {
            $role =new Role();
            $role->setName($roles);
            $role->setSlug('ROLE_'.strtoupper($roles));
            $role->setStatus(rand(0, 1));
            $role->setCreatedAt(new DateTimeImmutable('now'));

            $roleEntity[] = $role;
            $manager->persist($role);
        }

        /*****************USER ******************/

        $users = [
            [
                'login' => 'admin@admin.com',
                'password' => 'admin',
                'role' => '2'
            ],
            [
                'login' => 'organisateur@organisateur.com',
                'password' => 'organisateur',
                'role' => '1'
            ],
            [
                'login' => 'user@user.com',
                'password' => 'user',
                'role' => '0'
            ]
            ];

        foreach($users AS $currentUser)
        {
            $user = new User();
            $user->setEmail($currentUser['login']);
            $user->setRole($roleEntity[$currentUser['role']]);
            $user->setPassword($this->passwordHasher->hashPassword($user, $currentUser['password']));
            $user->setPseudo($faker->userName());
            $user->setLastname($faker->lastName());
            $user->setFirstname($faker->firstName());
            $user->setAddress($faker->secondaryAddress());
            $user->setPostalCode($faker->randomNumber(5, true));
            $user->setCity($faker->country());
            $user->setStatus(true);
            $userEntity[]= $user;
            $manager->persist($user);
        }
        
        $userEntity = [];
        for ($i = 1; $i<= 10; $i++) {
            $user = new User();
            $user->setEmail($faker->email());
            $user->setPassword($this->passwordHasher->hashPassword($user, $faker->password()));
            $user->setPseudo($faker->userName());
            $user->setLastname($faker->lastName());
            $user->setFirstname($faker->firstName());
            $user->setAddress($faker->secondaryAddress());
            $user->setPostalCode($faker->randomNumber(5, true));
            $user->setCity($faker->country());
            $user->setStatus(rand(0, 1));
            $user->setCreatedAt(new DateTimeImmutable('now'));
            $randomRole = $roleEntity[mt_rand(0, count($roleEntity) -1)];
            $user->setRole($randomRole);

            $userEntity[]= $user;
            $manager->persist($user);
        }

        /*****************GAME ******************/

        $gameEntity = [];
        
        for ($i=0; $i < 10; $i++) {
            $game = new Game();
            $title = $faker->words(2, true);
            $game->setTitle($title);
            $game->setSlug($this->slugger->slugify($title));
            $game->setAddress($faker->address());
            $game->setPostalCode($faker->randomNumber(5, true));
            $game->setCity($faker->country());
            $image = "https://picsum.photos/id/".mt_rand(1, 20)."/828/315";
            $response = $this->client->request('GET', $image);
            file_put_contents('./public/assets/images/games/'.$this->slugger->slugify($title).'.jpg', $response->getContent());
            $game->setImage($this->slugger->slugify($title).'.jpg');
            $game->setSummary($faker->text(30));
            $game->setStatus(rand(0, 1));
            $game->setIsTrashed(rand(0, 1));

            $int_game= mt_rand(1262055681,1646147483);
            $time = (new DateTimeImmutable)->setTimestamp($int_game);
            $game->setCreatedAt($time);
            $randomUser = $userEntity[mt_rand(0, count($userEntity) - 1)];
            $game->setUser($randomUser);

            $gameEntity[] = $game;
            $manager->persist($game);

            $checkpointEntity = [];
            for ($i=1; $i <= random_int(1, 8); $i++) {
                $checkpoint = new Checkpoint();
                $checkpoint->setTitle($faker->words(2, true));
                $checkpoint->setSuccessMessage($faker->text(5));
                $checkpoint->setOrderCheckpoint($i);
                $checkpoint->setIsTrashed(rand(0, 1));
                $int= mt_rand(0, 20000);
                $checkpoint->setCreatedAt((new DateTimeImmutable)->setTimestamp($int_game+$int));
    
                /* This is a random choice of a game from the array of games. */
                $checkpoint->setGame($game);
    
                $checkpointEntity[] = $checkpoint;
                $manager->persist($checkpoint);

                for ($i=1; $i <= random_int(1, 8); $i++) {
                    $enigma = new Enigma();
                    $enigma->setQuestion($questProvider->enigmes());
                    $enigma->setOrderEnigma($i);
                    $enigma->setIsTrashed(rand(0, 1));
                    $int= mt_rand(20000, 40000);
                    $enigma->setCreatedAt((new DateTimeImmutable)->setTimestamp($int_game+$int));
                    
                    // ajout de answer dans enigma
                    $nbAnswer = 3;
                    for ($a=1; $a <= $nbAnswer; $a++) {
                        $answer = new Answer();
                        $answer->setAnswer($faker->words(1, true));
                        $answer->setStatus(rand(0, 1));
                        $int= mt_rand(40000, 50000);
                        $answer->setCreatedAt((new DateTimeImmutable)->setTimestamp($int_game+$int));
        
                        $manager->persist($answer);
                        $enigma->addAnswer($answer);
                    }
        
                    $enigma->setCheckpoint($checkpoint);
        
                    $enigmaEntity[] = $enigma;
                    $manager->persist($enigma);
                }
            }

            /*****************INSTANCE ******************/
            for ($j=0; $j < random_int(0, 5); $j++) {
                $newInstance = new Instance();
                $newInstance->setTitle($faker->words(2, true));
                $newInstance->setMessage($faker->text(100));

                $int_instance= mt_rand(50000,5000000);
                $timestamp_instance_begin = $int_game+$int_instance;
                $time_begin_instance = (new DateTimeImmutable)->setTimestamp($timestamp_instance_begin);
                $newInstance->setStartAt($time_begin_instance);
                $timestamp_instance_end = $int_game+$int_instance*2;
                $time_end_instance = (new DateTimeImmutable)->setTimestamp($timestamp_instance_end);
                $newInstance->setEndAt($time_end_instance);
                $newInstance->setIsTrashed(rand(0, 1));
    
                $newInstance->setGame($game);
    
    
                $instanceEntity[] = $newInstance;
                $manager->persist($newInstance);

                for ($k=0; $k <= random_int(0, 3); $k++) {
                    $newRound = new Round();
                    $int_round= mt_rand($timestamp_instance_begin,$timestamp_instance_end);
                    $timestamp_round_begin= $int_round;
                    $newRound->setStartAt((new DateTimeImmutable)->setTimestamp($timestamp_round_begin));
                    $timestamp_round_end = mt_rand($timestamp_round_begin,$timestamp_instance_end);;
                    $newRound->setEndAt((new DateTimeImmutable)->setTimestamp($timestamp_round_end));
                
                    /* This is a random choice of a user from the array of users. */
                    $randomUser = $userEntity[mt_rand(0, count($userEntity) -1)];
                    $newRound->setUser($randomUser);
        
                    $newRound->setInstance($newInstance);
                
                    $roundEntity[] = $newRound;
                    $manager->persist($newRound);

                    for ($l = 0; $l < 5; $l++) {
                        $scanQr = new ScanQR();
                        $int_scan= mt_rand($timestamp_round_begin,$timestamp_round_end);
                        $scanQr->setScanAt((new DateTimeImmutable)->setTimestamp($int_scan));
                        $randomCheckpoint = $checkpointEntity[mt_rand(0, count($checkpointEntity) - 1)];
                        $scanQr->setCheckpoint($randomCheckpoint);
            
                        $scanQr->setRound($newRound);
            
            
                        $scanqrEntity[] =$scanQr;
                        $manager->persist($scanQr);
                    }
        
                }
            }
        }
        
        /*****************INSTANCE ******************/

        /*$instanceEntity = [];
        for ($i=0; $i < 10; $i++) {
            $newInstance = new Instance();
            $newInstance->setTitle($faker->words(2, true));
            $newInstance->setSlug($faker->words(2, true));
            $newInstance->setMessage($faker->text(100));
            $newInstance->setStartAt(new DateTimeImmutable('now'));
            $newInstance->setEndAt(new DateTimeImmutable('now'));
            $newInstance->setIsTrashed(rand(0, 1));

            $randomGame = $gameEntity[mt_rand(0, count($gameEntity) - 1)];
            $newInstance->setGame($randomGame);


            $instanceEntity[] = $newInstance;
            $manager->persist($newInstance);
        }*/
                
        /*****************ROUND ******************/ 

        /*$roundEntity = [];
        for ($i=0; $i <= 6; $i++) {
            $newRound = new Round();
            $newRound->setStartAt(new DateTimeImmutable('now'));
        
            /* This is a random choice of a user from the array of users. */
        /*    $randomUser = $userEntity[mt_rand(0, count($userEntity) -1)];
            $newRound->setUser($randomUser);

            $randomInstance = $instanceEntity[mt_rand(0, count($instanceEntity) -1)];
            $newRound->setInstance($randomInstance);
        
            $roundEntity[] = $newRound;
            $manager->persist($newRound);

            }*/

        /*****************CHECKPOINT ******************/

        /*$checkpointEntity = [];
        for ($i=1; $i <= 10; $i++) {
            $checkpoint = new Checkpoint();
            $checkpoint->setTitle($faker->words(2, true));
            $checkpoint->setSuccessMessage($faker->text(5));
            $checkpoint->setOrderCheckpoint($i);
            $checkpoint->setIsTrashed(rand(0, 1));
            $checkpoint->setCreatedAt(new DateTimeImmutable('now'));

            /* This is a random choice of a game from the array of games. */
        /*    $randomGame = $gameEntity[mt_rand(0, count($gameEntity) -1)];
            $checkpoint->setGame($randomGame);

            $checkpointEntity[] = $checkpoint;
            $manager->persist($checkpoint);
        }*/

        /*****************ENIGMA ******************/
        /*$enigmaEntity = [];
        for ($i=1; $i <= 20; $i++) {
            $enigma = new Enigma();
            $enigma->setQuestion($questProvider->enigmes());
            $enigma->setOrderEnigma($i);
            $enigma->setIsTrashed(rand(0, 1));
            $enigma->setCreatedAt(new DateTimeImmutable('now'));
            
            // ajout de answer dans enigma
            $nbAnswer = 3;
            for ($a=1; $a <= $nbAnswer; $a++) {
                $answer = new Answer();
                $answer->setAnswer($faker->words(1, true));
                $answer->setStatus(rand(0, 1));
                $answer->setCreatedAt(new DateTimeImmutable('now'));

                $manager->persist($answer);
                $enigma->addAnswer($answer);
            }


            $randomCheckpoint = $checkpointEntity[mt_rand(0, count($checkpointEntity) -1)];
            $enigma->setCheckpoint($randomCheckpoint);

            $enigmaEntity[] = $enigma;
            $manager->persist($enigma);
        }*/

        /*****************SCANQR ******************/
        /*$scanqrEntity = [];
        for ($i = 0; $i < 5; $i++) {
            $scanQr = new ScanQR();
        
            $scanQr->setScanAt(new DateTimeImmutable('now'));
            $randomCheckpoint = $checkpointEntity[mt_rand(0, count($checkpointEntity) - 1)];
            $scanQr->setCheckpoint($randomCheckpoint);

            $randomRound = $roundEntity[mt_rand(0, count($roundEntity) - 1)];
            $scanQr->setRound($randomRound);


            $scanqrEntity[] =$scanQr;
            $manager->persist($scanQr);
        }*/

        $manager->flush();
    }
}