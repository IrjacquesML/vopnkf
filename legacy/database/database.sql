-- Base de données pour VOP Etude Biblique par Correspondance
CREATE DATABASE IF NOT EXISTS vop_etude CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE vop_etude;

-- Ajouter le champ role à la table utilisateurs (si elle existe déjà)
ALTER TABLE utilisateurs ADD COLUMN IF NOT EXISTS role ENUM('utilisateur', 'admin') DEFAULT 'utilisateur' AFTER mot_de_passe;

-- Ajouter le champ date_completion à la table progression_lecons (si elle existe déjà)
ALTER TABLE progression_lecons ADD COLUMN IF NOT EXISTS date_completion TIMESTAMP NULL AFTER date_debut;

-- Table des utilisateurs
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prenom VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    mot_de_passe VARCHAR(255) NOT NULL,
    role ENUM('utilisateur', 'admin') DEFAULT 'utilisateur',
    pays VARCHAR(100) NULL,
    province VARCHAR(100) NULL,
    ville VARCHAR(100) NULL,
    adresse_complete TEXT NULL,
    telephone VARCHAR(20) NULL,
    photo_profil VARCHAR(255) NULL,
    langue_preferee VARCHAR(10) DEFAULT 'fr',
    date_inscription TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    derniere_connexion TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Ajouter les colonnes d'adresse, photo et langue si la table existe déjà
ALTER TABLE utilisateurs 
ADD COLUMN IF NOT EXISTS pays VARCHAR(100) NULL AFTER role,
ADD COLUMN IF NOT EXISTS province VARCHAR(100) NULL AFTER pays,
ADD COLUMN IF NOT EXISTS ville VARCHAR(100) NULL AFTER province,
ADD COLUMN IF NOT EXISTS adresse_complete TEXT NULL AFTER ville,
ADD COLUMN IF NOT EXISTS telephone VARCHAR(20) NULL AFTER adresse_complete,
ADD COLUMN IF NOT EXISTS photo_profil VARCHAR(255) NULL AFTER telephone,
ADD COLUMN IF NOT EXISTS langue_preferee VARCHAR(10) DEFAULT 'fr' AFTER photo_profil;

-- Table des catégories
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    description TEXT,
    ordre INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des leçons
CREATE TABLE IF NOT EXISTS lecons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT NOT NULL,
    titre VARCHAR(200) NOT NULL,
    contenu TEXT,
    ordre INT DEFAULT 0,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categorie_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des questions
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lecon_id INT NOT NULL,
    question TEXT NOT NULL,
    ordre INT DEFAULT 0,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des options de réponse
CREATE TABLE IF NOT EXISTS options_reponse (
    id INT AUTO_INCREMENT PRIMARY KEY,
    question_id INT NOT NULL,
    texte_option TEXT NOT NULL,
    est_correcte TINYINT(1) DEFAULT 0,
    ordre INT DEFAULT 0,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table de progression des utilisateurs
CREATE TABLE IF NOT EXISTS progression_lecons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    lecon_id INT NOT NULL,
    statut ENUM('non_commence', 'en_cours', 'termine') DEFAULT 'non_commence',
    score DECIMAL(5,2) DEFAULT NULL,
    date_debut TIMESTAMP NULL,
    date_completion TIMESTAMP NULL,
    date_fin TIMESTAMP NULL,
    UNIQUE KEY unique_user_lesson (utilisateur_id, lecon_id),
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des réponses des utilisateurs
CREATE TABLE IF NOT EXISTS reponses_utilisateurs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    question_id INT NOT NULL,
    option_id INT NOT NULL,
    lecon_id INT NOT NULL,
    est_correcte TINYINT(1) DEFAULT 0,
    date_reponse TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE,
    FOREIGN KEY (option_id) REFERENCES options_reponse(id) ON DELETE CASCADE,
    FOREIGN KEY (lecon_id) REFERENCES lecons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des versets bibliques
CREATE TABLE IF NOT EXISTS versets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reference VARCHAR(100) NOT NULL,
    livre VARCHAR(50) NOT NULL,
    chapitre INT NOT NULL,
    verset INT NOT NULL,
    texte TEXT NOT NULL,
    version VARCHAR(20) DEFAULT 'LSG',
    UNIQUE KEY unique_reference (livre, chapitre, verset, version)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertion de données de test
INSERT INTO categories (nom, description, ordre) VALUES
('Introduction à la Bible', 'Découvrez les fondements de la foi chrétienne', 1),
('La vie de Jésus', 'Explorez la vie et les enseignements de Jésus-Christ', 2),
('Les prophéties bibliques', 'Comprenez les prophéties et leur accomplissement', 3);

-- Leçons pour la catégorie 1
INSERT INTO lecons (categorie_id, titre, contenu, ordre) VALUES
(1, 'Qu\'est-ce que la Bible?', 'La Bible est la Parole de Dieu, inspirée par le Saint-Esprit. Elle contient 66 livres écrits sur une période de 1500 ans par environ 40 auteurs différents. Malgré cette diversité, elle présente un message cohérent: le plan de Dieu pour sauver l\'humanité.', 1),
(1, 'Comment étudier la Bible?', 'L\'étude de la Bible nécessite de la prière, de la méditation et de la persévérance. Il est important de lire dans son contexte et de comparer les Écritures avec les Écritures.', 2),
(1, 'La puissance de la Parole', 'La Parole de Dieu est vivante et efficace. Elle transforme les cœurs et les vies de ceux qui la reçoivent avec foi.', 3);

-- Leçons pour la catégorie 2
INSERT INTO lecons (categorie_id, titre, contenu, ordre) VALUES
(2, 'La naissance de Jésus', 'Jésus est né à Bethléhem, accomplissant les prophéties de l\'Ancien Testament. Sa naissance miraculeuse démontre qu\'Il est le Fils de Dieu.', 1),
(2, 'Le ministère de Jésus', 'Pendant trois ans, Jésus a enseigné, guéri les malades et accompli des miracles, démontrant l\'amour de Dieu pour l\'humanité.', 2);

-- Questions pour la leçon 1
INSERT INTO questions (lecon_id, question, ordre) VALUES
(1, 'Combien de livres la Bible contient-elle?', 1),
(1, 'Qui a inspiré les auteurs de la Bible?', 2),
(1, 'Quel est le message principal de la Bible?', 3);

-- Options pour question 1
INSERT INTO options_reponse (question_id, texte_option, est_correcte, ordre) VALUES
(1, '66 livres', 1, 1),
(1, '39 livres', 0, 2),
(1, '27 livres', 0, 3),
(1, '100 livres', 0, 4);

-- Options pour question 2
INSERT INTO options_reponse (question_id, texte_option, est_correcte, ordre) VALUES
(2, 'Le Saint-Esprit', 1, 1),
(2, 'Les anges', 0, 2),
(2, 'Les prophètes eux-mêmes', 0, 3),
(2, 'Les rois', 0, 4);

-- Options pour question 3
INSERT INTO options_reponse (question_id, texte_option, est_correcte, ordre) VALUES
(3, 'Le plan de Dieu pour sauver l\'humanité', 1, 1),
(3, 'L\'histoire des rois d\'Israël', 0, 2),
(3, 'Les règles de vie en société', 0, 3),
(3, 'La création du monde uniquement', 0, 4);

-- Quelques versets bibliques populaires
INSERT INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Jean 3:16', 'Jean', 3, 16, 'Car Dieu a tant aimé le monde qu\'il a donné son Fils unique, afin que quiconque croit en lui ne périsse point, mais qu\'il ait la vie éternelle.', 'LSG'),
('Romains 3:23', 'Romains', 3, 23, 'Car tous ont péché et sont privés de la gloire de Dieu.', 'LSG'),
('Romains 6:23', 'Romains', 6, 23, 'Car le salaire du péché, c\'est la mort; mais le don gratuit de Dieu, c\'est la vie éternelle en Jésus-Christ notre Seigneur.', 'LSG'),
('Éphésiens 2:8', 'Éphésiens', 2, 8, 'Car c\'est par la grâce que vous êtes sauvés, par le moyen de la foi. Et cela ne vient pas de vous, c\'est le don de Dieu.', 'LSG'),
('1 Jean 1:9', '1 Jean', 1, 9, 'Si nous confessons nos péchés, il est fidèle et juste pour nous les pardonner, et pour nous purifier de toute iniquité.', 'LSG'),
('Matthieu 11:28', 'Matthieu', 11, 28, 'Venez à moi, vous tous qui êtes fatigués et chargés, et je vous donnerai du repos.', 'LSG'),
('Psaumes 23:1', 'Psaumes', 23, 1, 'L\'Éternel est mon berger: je ne manquerai de rien.', 'LSG'),
('Philippiens 4:13', 'Philippiens', 4, 13, 'Je puis tout par celui qui me fortifie.', 'LSG');

-- Table des demandes de prière
CREATE TABLE IF NOT EXISTS demandes_priere (
    id INT AUTO_INCREMENT PRIMARY KEY,
    utilisateur_id INT NOT NULL,
    sujet VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    est_anonyme TINYINT(1) DEFAULT 0,
    statut ENUM('en_attente', 'en_priere', 'exaucee') DEFAULT 'en_attente',
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (utilisateur_id) REFERENCES utilisateurs(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Table des traductions de contenu
CREATE TABLE IF NOT EXISTS traductions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type_contenu ENUM('lecon', 'categorie', 'question', 'interface') NOT NULL,
    contenu_id INT NULL,
    cle_texte VARCHAR(100) NULL,
    texte_original TEXT NOT NULL,
    langue VARCHAR(10) NOT NULL,
    texte_traduit TEXT NOT NULL,
    date_creation TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_modification TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_type_contenu (type_contenu, contenu_id, langue),
    INDEX idx_cle_langue (cle_texte, langue)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
