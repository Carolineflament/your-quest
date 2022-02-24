<?php

namespace App\DataFixtures;

use App\Entity\Game;
use App\Entity\Instance;
use App\Entity\Role;
use App\Entity\Round;
use App\Entity\User;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');

        /*****************ROLE ******************/

        $roleEntity = [];
        $roleName = [
            'Joueur', 'Organisateur', 'Admin'
        ];
        foreach ($roleName as $roles) {
            $role =new Role();
            $role->setName($roles);
            $role->setSlug($faker->words(2, true));
            $role->setStatus(rand(0, 1));
            $role->setCreatedAt(new DateTimeImmutable('now'));

            $roleEntity[] = $role;
            $manager->persist($role);
        }

        /*****************USER ******************/
        
        $userEntity = [];
        for ($i = 1; $i<= 15; $i++) {
            $user = new User();
            $user->setEmail($faker->email());
            $user->setPassword($faker->password());
            $user->setUsername($faker->userName());
            $user->setLastname($faker->lastName());
            $user->setFirstname($faker->firstName());
            $user->setAddress($faker->secondaryAddress());
            $user->setPostalCode($faker->randomNumber(5, true));
            $user->setCity($faker->country());
            $user->setStatus(rand(0, 1));
            $user->setCreatedAt(new DateTimeImmutable('now'));
            $randomRole = $roleEntity[mt_rand(0, count($roleEntity) - 1)];
            $user->setRole($randomRole);

            $userEntity[]= $user;
            $manager->persist($user);
        }

        /*****************GAME ******************/

        $gameEntity = [];
        for ($i=0; $i < 10; $i++) {
            $game = new Game();
            $game->setTitle($faker->words(2, true));
            $game->setSlug($faker->words(2, true));
            $game->setAddress($faker->secondaryAddress());
            $game->setPostalCode($faker->randomNumber(5, true));
            $game->setCity($faker->country());
            $game->setImage('https://picsum.photos/id/'.mt_rand(1, 20).'/828/315');
            $game->setSummary($faker->text(30));
            $game->setStatus(rand(0, 1));
            $game->setCreatedAt(new DateTimeImmutable('now'));

            $randomUser = $userEntity[mt_rand(0, count($userEntity) - 1)];
            $game->setUser($randomUser);

            $gameEntity[] = $game;

            $manager->persist($game);
        }

        /*****************INSTANCE ******************/
        $instanceEntity = [];
        for ($i=0; $i <= mt_rand(1, 5) ; $i++) {
            $instance = new Instance();
            $instance->setTitle($faker->words(2, true));
            $instance->setSlug($faker->words(2, true));
            $instance->setMessage($faker->text(100));
            $instance->setStartAt(new DateTimeImmutable('now'));
            $instance->setEndAt(new DateTimeImmutable('now'));

            /* This is a random choice of a game from the array of games. */
            $randomgame = $gameEntity[mt_rand(0, count($gameEntity) - 1)];
            $instance->setGame($randomgame);

            $instanceEntity[] = $instance;
            $manager->persist($instance);
        }

        /*****************ROUND ******************/
        $roundEntity = [];
        for ($i=0; $i <= mt_rand(0, 5); $i++) {
            $round = new Round();
            $round->setStartAt(new DateTimeImmutable('now'));
            $round->setEndAt(new DateTimeImmutable('now'));

            $randomInstance = $instanceEntity[mt_rand(0, count($instanceEntity) - 1)];
            $round->setInstance($randomInstance);

            $randomUser = $userEntity[mt_rand(0, count($userEntity) - 1)];
            $round->setUser($randomUser);

            $roundEntity[] = $round;
            $manager->persist($round);

            $manager->flush();
        }
    }
}