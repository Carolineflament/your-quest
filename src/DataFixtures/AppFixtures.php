<?php

namespace App\DataFixtures;

use App\Entity\Game;
use DateTimeImmutable;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Faker\Factory::create('fr_FR');



        $gameEntity = [];
        for ($i=0; $i < 10; $i++) {
            $game = new Game();

            $game->setTitle($faker->words(2, true));
            $game->setSlug($faker->words(2, true));
            $game->setAddress($faker->secondaryAddress());
            $game->setPostalCode($faker->randomNumber(5, true));
            $game->setCity($faker->country());
            $game->setImage('https://picsum.photos/id/'.mt_rand(1, 20).'/303/424');
            $game->setSummary($faker->text(30));
            $game->setStatus(rand(0, 1));
            $game->setCreatedAt(new DateTimeImmutable('now'));
            $game->setUser();

        

            $gameEntity[] = $game;

            $manager->persist($game);
            $manager->flush();
        }
    }
}