<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Lecon;
use App\Models\OptionReponse;
use App\Models\Parametre;
use App\Models\Question;
use App\Models\User;
use App\Models\Verset;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        Parametre::saveCertificateSignatories(Parametre::defaultSignatories());

        User::factory()->admin()->create([
            'nom' => 'Admin',
            'prenom' => 'VOP',
            'email' => 'vopnkf@admin.com',
            'password' => Hash::make('vopnkf@admin.org'),
        ]);

        User::factory()->create([
            'nom' => 'Dupont',
            'prenom' => 'Jean',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $intro = Categorie::create([
            'nom' => 'Introduction à la Bible',
            'description' => 'Découvrez les fondements de la foi chrétienne',
            'ordre' => 1,
        ]);

        $vieJesus = Categorie::create([
            'nom' => 'La vie de Jésus',
            'description' => 'Explorez la vie et les enseignements de Jésus-Christ',
            'ordre' => 2,
        ]);

        Categorie::create([
            'nom' => 'Les prophéties bibliques',
            'description' => 'Comprenez les prophéties et leur accomplissement',
            'ordre' => 3,
        ]);

        $lecon1 = Lecon::create([
            'categorie_id' => $intro->id,
            'titre' => "Qu'est-ce que la Bible?",
            'contenu' => "La Bible est la Parole de Dieu, inspirée par le Saint-Esprit. Elle contient 66 livres écrits sur une période de 1500 ans par environ 40 auteurs différents. Malgré cette diversité, elle présente un message cohérent: le plan de Dieu pour sauver l'humanité.",
            'ordre' => 1,
        ]);

        Lecon::create([
            'categorie_id' => $intro->id,
            'titre' => 'Comment étudier la Bible?',
            'contenu' => "L'étude de la Bible nécessite de la prière, de la méditation et de la persévérance. Il est important de lire dans son contexte et de comparer les Écritures avec les Écritures.",
            'ordre' => 2,
        ]);

        Lecon::create([
            'categorie_id' => $intro->id,
            'titre' => 'La puissance de la Parole',
            'contenu' => 'La Parole de Dieu est vivante et efficace. Elle transforme les cœurs et les vies de ceux qui la reçoivent avec foi.',
            'ordre' => 3,
        ]);

        Lecon::create([
            'categorie_id' => $vieJesus->id,
            'titre' => 'La naissance de Jésus',
            'contenu' => "Jésus est né à Bethléhem, accomplissant les prophéties de l'Ancien Testament. Sa naissance miraculeuse démontre qu'Il est le Fils de Dieu.",
            'ordre' => 1,
        ]);

        Lecon::create([
            'categorie_id' => $vieJesus->id,
            'titre' => 'Le ministère de Jésus',
            'contenu' => "Pendant trois ans, Jésus a enseigné, guéri les malades et accompli des miracles, démontrant l'amour de Dieu pour l'humanité.",
            'ordre' => 2,
        ]);

        $q1 = Question::create([
            'lecon_id' => $lecon1->id,
            'question' => 'Combien de livres la Bible contient-elle?',
            'ordre' => 1,
        ]);
        $q2 = Question::create([
            'lecon_id' => $lecon1->id,
            'question' => 'Qui a inspiré les auteurs de la Bible?',
            'ordre' => 2,
        ]);
        $q3 = Question::create([
            'lecon_id' => $lecon1->id,
            'question' => 'Quel est le message principal de la Bible?',
            'ordre' => 3,
        ]);

        foreach ([
            [$q1, '66 livres', true, 1],
            [$q1, '39 livres', false, 2],
            [$q1, '27 livres', false, 3],
            [$q1, '100 livres', false, 4],
            [$q2, 'Le Saint-Esprit', true, 1],
            [$q2, 'Les anges', false, 2],
            [$q2, 'Les prophètes eux-mêmes', false, 3],
            [$q2, 'Les rois', false, 4],
            [$q3, "Le plan de Dieu pour sauver l'humanité", true, 1],
            [$q3, "L'histoire des rois d'Israël", false, 2],
            [$q3, 'Les règles de vie en société', false, 3],
            [$q3, 'La création du monde uniquement', false, 4],
        ] as [$question, $texte, $correcte, $ordre]) {
            OptionReponse::create([
                'question_id' => $question->id,
                'texte_option' => $texte,
                'est_correcte' => $correcte,
                'ordre' => $ordre,
            ]);
        }

        $versets = [
            ['Jean 3:16', 'Jean', 3, 16, "Car Dieu a tant aimé le monde qu'il a donné son Fils unique, afin que quiconque croit en lui ne périsse point, mais qu'il ait la vie éternelle."],
            ['Romains 3:23', 'Romains', 3, 23, 'Car tous ont péché et sont privés de la gloire de Dieu.'],
            ['Romains 6:23', 'Romains', 6, 23, "Car le salaire du péché, c'est la mort; mais le don gratuit de Dieu, c'est la vie éternelle en Jésus-Christ notre Seigneur."],
            ['Éphésiens 2:8', 'Éphésiens', 2, 8, "Car c'est par la grâce que vous êtes sauvés, par le moyen de la foi. Et cela ne vient pas de vous, c'est le don de Dieu."],
            ['1 Jean 1:9', '1 Jean', 1, 9, 'Si nous confessons nos péchés, il est fidèle et juste pour nous les pardonner, et pour nous purifier de toute iniquité.'],
            ['Matthieu 11:28', 'Matthieu', 11, 28, 'Venez à moi, vous tous qui êtes fatigués et chargés, et je vous donnerai du repos.'],
            ['Psaumes 23:1', 'Psaumes', 23, 1, "L'Éternel est mon berger: je ne manquerai de rien."],
            ['Philippiens 4:13', 'Philippiens', 4, 13, 'Je puis tout par celui qui me fortifie.'],
        ];

        foreach ($versets as [$reference, $livre, $chapitre, $verset, $texte]) {
            Verset::create([
                'reference' => $reference,
                'livre' => $livre,
                'chapitre' => $chapitre,
                'verset' => $verset,
                'texte' => $texte,
                'version' => 'LSG',
            ]);
        }
    }
}
