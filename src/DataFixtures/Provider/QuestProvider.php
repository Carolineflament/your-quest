<?php

namespace App\DataFixtures\Provider;

class QuestProvider
{
    private $enigmas = [
    'Un fermier a 10 lapins, 20 chevaux et 40 cochons. Si on appelle chevaux aux cochons. Combien de chevaux aura-t-il ?',
    'Qu\'est-ce qui fait que le numéro 542.986.731 soit unique ?',
    'Si tu me dis la vérité, je te tuerai avec mon épée. Si tu me mens, je te tuerai avec un sort. Que dois-tu dire pour te sauver ?',
    'Dans une course, vous doublez le second juste avant la ligne d\'arrivée. Quelle est votre position finale ?',
    'Avant hier Carla avait 15 ans. L\'année qui vient elle aura 18 ans. On est quel jour ?',
    'Qu\'est-ce qui peut voyager dans le monde entier en restant dans un coin ?',
    'Combien d\'animaux vous avez à la maison en sachant que tous sont des chats à part deux, que tous sont des chiens à part deux et tous sont des tortues à part deux.',
    'Un berger a 15 moutons et ils meurent tous à part 9. Combien de moutons est-ce qu\'il a ?',
    'Il y\'en a un dans une minute, deux dans un moment et aucun dans une heure. De quoi s\'agit-il ?',
    'Quelle invention permet de regarder au travers d\'un mur ?',
    'Un homme peut-il se marier avec la sœur de sa veuve ?',
    'Un avion s\'écrase dans les Pyrénées, entre l\'Espagne et la France, tout le monde meure. A quel hôpital faut-il amener les survivants ?',
    'J\'ai 100 canards, mais deux dans une boîte. Combien y-a-t-il de becs et de pattes ?',
    'Si dans un aquarium il y a 10 poissons et 5 d\'entre-eux se noient. Combien reste-t-il de poissons ?',
    'J\'ai trois pommes et tu m\'en enlèves 2. Combien est-ce que t\'as de pommes ?',
    'De quelle couleur sont les manches du gilet rouge de Diane ?',
    'Combien d\'animaux Moïse a amené dans son arche ?',
    'Il y a 6 personnes dans une pièce, un homme arrive et en tue 4. Combien y-a-t-il de personnes ?',
    'Vous frappez une balle, elle s\'éloigne de 5 mètres, mais elle revient directement à vous sans que personne vous le renvoie. Pourquoi ?',
   'Un ours marche 10 km vers le Sud, 10 vers l\'Est et 10 vers le Nord, après tout ce chemin il revient à l\'endroit duquel il est parti. De quelle couleur est l\'ours ?',
  ];
    /**
     * Retourne une egnime au hasard
     */
    public function enigmes()
    {
        return $this->enigmas[array_rand($this->enigmas)];
    }
}