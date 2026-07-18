-- ============================================================
-- Migration : Versets Bibliques (Louis Segond 1910 - LSG)
-- Couvre toutes les références des leçons et questions VOP
-- Usage : SOURCE database/migration_versets.sql;
-- ============================================================

SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;

-- ============================================================
-- GENÈSE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Genèse 1:1',  'Genèse', 1, 1,  'Au commencement, Dieu créa les cieux et la terre.',
 'LSG'),
('Genèse 1:5',  'Genèse', 1, 5,  'Dieu appela la lumière jour, et il appela les ténèbres nuit. Ainsi, il y eut un soir, et il y eut un matin : ce fut le premier jour.',
 'LSG'),
('Genèse 1:8',  'Genèse', 1, 8,  'Dieu appela l\'étendue ciel. Ainsi, il y eut un soir, et il y eut un matin : ce fut le second jour.',
 'LSG'),
('Genèse 1:13', 'Genèse', 1, 13, 'Ainsi, il y eut un soir, et il y eut un matin : ce fut le troisième jour.',
 'LSG'),
('Genèse 1:26', 'Genèse', 1, 26, 'Puis Dieu dit : Faisons l\'homme à notre image, selon notre ressemblance, et qu\'il domine sur les poissons de la mer, sur les oiseaux du ciel, sur le bétail, sur toute la terre, et sur tous les reptiles qui rampent sur la terre.',
 'LSG'),
('Genèse 2:1',  'Genèse', 2, 1,  'Ainsi furent achevés les cieux et la terre, et toute leur armée.',
 'LSG'),
('Genèse 2:2',  'Genèse', 2, 2,  'Dieu acheva au septième jour son œuvre, qu\'il avait faite : et il se reposa au septième jour de toute son œuvre, qu\'il avait faite.',
 'LSG'),
('Genèse 2:3',  'Genèse', 2, 3,  'Dieu bénit le septième jour, et il le sanctifia, parce qu\'en ce jour il se reposa de toute son œuvre qu\'il avait créée en la faisant.',
 'LSG'),
('Genèse 2:7',  'Genèse', 2, 7,  'L\'Éternel Dieu forma l\'homme de la poussière de la terre, il souffla dans ses narines un souffle de vie et l\'homme devint un être vivant.',
 'LSG'),
('Genèse 2:17', 'Genèse', 2, 17, 'Mais tu ne mangeras pas de l\'arbre de la connaissance du bien et du mal, car le jour où tu en mangeras, tu mourras certainement.',
 'LSG'),
('Genèse 3:1',  'Genèse', 3, 1,  'Le serpent était le plus rusé de tous les animaux des champs, que l\'Éternel Dieu avait faits. Il dit à la femme : Dieu a-t-il réellement dit : Vous ne mangerez pas de tous les arbres du jardin ?',
 'LSG'),
('Genèse 3:4',  'Genèse', 3, 4,  'Le serpent dit à la femme : Vous ne mourrez point ;',
 'LSG');

-- ============================================================
-- EXODE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Exode 12:7',  'Exode', 12, 7,  'Ils prendront de son sang, et en mettront sur les deux poteaux et sur le linteau de la porte des maisons où ils le mangeront.',
 'LSG'),
('Exode 12:13', 'Exode', 12, 13, 'Le sang vous servira de signe sur les maisons où vous serez : je verrai le sang, et je passerai par-dessus vous, et il n\'y aura point de plaie qui vous détruise, quand je frapperai le pays d\'Égypte.',
 'LSG'),
('Exode 20:3',  'Exode', 20, 3,  'Tu n\'auras pas d\'autres dieux devant ma face.',
 'LSG'),
('Exode 20:4',  'Exode', 20, 4,  'Tu ne te feras point d\'image taillée, ni de représentation quelconque des choses qui sont en haut dans les cieux, qui sont en bas sur la terre, et qui sont dans les eaux plus bas que la terre.',
 'LSG'),
('Exode 20:5',  'Exode', 20, 5,  'Tu ne te prosterneras point devant elles, et tu ne les serviras point ; car moi, l\'Éternel, ton Dieu, je suis un Dieu jaloux, qui punit l\'iniquité des pères sur les enfants jusqu\'à la troisième et la quatrième génération de ceux qui me haïssent,',
 'LSG'),
('Exode 20:7',  'Exode', 20, 7,  'Tu ne prendras point le nom de l\'Éternel, ton Dieu, en vain ; car l\'Éternel ne laissera point impuni celui qui prendra son nom en vain.',
 'LSG'),
('Exode 20:8',  'Exode', 20, 8,  'Souviens-toi du jour du repos, pour le sanctifier.',
 'LSG'),
('Exode 20:9',  'Exode', 20, 9,  'Tu travailleras six jours, et tu feras tout ton ouvrage.',
 'LSG'),
('Exode 20:10', 'Exode', 20, 10, 'Mais le septième jour est le jour du repos de l\'Éternel, ton Dieu : tu ne feras aucun ouvrage, ni toi, ni ton fils, ni ta fille, ni ton serviteur, ni ta servante, ni ton bétail, ni l\'étranger qui est dans tes portes.',
 'LSG'),
('Exode 20:11', 'Exode', 20, 11, 'Car en six jours l\'Éternel a fait les cieux, la terre et la mer, et tout ce qui y est contenu, et il s\'est reposé le septième jour : c\'est pourquoi l\'Éternel a béni le jour du repos et l\'a sanctifié.',
 'LSG'),
('Exode 20:12', 'Exode', 20, 12, 'Honore ton père et ta mère, afin que tes jours se prolongent dans le pays que l\'Éternel, ton Dieu, te donne.',
 'LSG'),
('Exode 20:13', 'Exode', 20, 13, 'Tu ne tueras point.',
 'LSG'),
('Exode 20:14', 'Exode', 20, 14, 'Tu ne commettras point d\'adultère.',
 'LSG'),
('Exode 20:15', 'Exode', 20, 15, 'Tu ne déroberas point.',
 'LSG'),
('Exode 20:16', 'Exode', 20, 16, 'Tu ne diras point de faux témoignage contre ton prochain.',
 'LSG'),
('Exode 20:17', 'Exode', 20, 17, 'Tu ne convoiteras point la maison de ton prochain ; tu ne convoiteras point la femme de ton prochain, ni son serviteur, ni sa servante, ni son bœuf, ni son âne, ni aucune chose qui appartienne à ton prochain.',
 'LSG'),
('Exode 25:8',  'Exode', 25, 8,  'Ils me feront un sanctuaire, et j\'habiterai au milieu d\'eux.',
 'LSG'),
('Exode 31:13', 'Exode', 31, 13, 'Tu parleras aux enfants d\'Israël, et tu leur diras : Vous observerez mes sabbats, car c\'est un signe entre moi et vous, pour vos générations, afin qu\'on sache que moi, l\'Éternel, je vous sanctifie.',
 'LSG'),
('Exode 31:16', 'Exode', 31, 16, 'Les enfants d\'Israël observeront le sabbat ; ils le célébreront, eux et leurs descendants, comme une alliance perpétuelle.',
 'LSG'),
('Exode 31:17', 'Exode', 31, 17, 'C\'est un signe perpétuel entre moi et les enfants d\'Israël ; car en six jours l\'Éternel a fait les cieux et la terre, et le septième jour il a chômé et pris du repos.',
 'LSG'),
('Exode 34:6',  'Exode', 34, 6,  'L\'Éternel passa devant lui, et proclama : L\'Éternel, l\'Éternel, Dieu miséricordieux et compatissant, lent à la colère, riche en bonté et en fidélité,',
 'LSG'),
('Exode 34:7',  'Exode', 34, 7,  'qui conserve sa grâce à des milliers, qui pardonne l\'iniquité, la rébellion et le péché, mais qui ne tient point le coupable pour innocent, qui punit l\'iniquité des pères sur les enfants et sur les enfants des enfants jusqu\'à la troisième et la quatrième génération !',
 'LSG');

-- ============================================================
-- LÉVITIQUE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Lévitique 23:6',  'Lévitique', 23, 6,  'Le quinzième jour de ce même mois, c\'est la fête des pains sans levain en l\'honneur de l\'Éternel ; pendant sept jours vous mangerez des pains sans levain.',
 'LSG'),
('Lévitique 23:7',  'Lévitique', 23, 7,  'Le premier jour, vous aurez une sainte convocation ; vous ne ferez aucun ouvrage servile.',
 'LSG'),
('Lévitique 23:8',  'Lévitique', 23, 8,  'Vous offrirez pendant sept jours des sacrifices consumés par le feu en l\'honneur de l\'Éternel. Le septième jour, il y aura une sainte convocation ; vous ne ferez aucun ouvrage servile.',
 'LSG'),
('Lévitique 23:34', 'Lévitique', 23, 34, 'Parle aux enfants d\'Israël, et dis-leur : Le quinzième jour de ce septième mois, c\'est la fête des tabernacles, pendant sept jours, en l\'honneur de l\'Éternel.',
 'LSG'),
('Lévitique 23:35', 'Lévitique', 23, 35, 'Le premier jour, il y aura une sainte convocation ; vous ne ferez aucun ouvrage servile.',
 'LSG');

-- ============================================================
-- NOMBRES
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Nombres 28:16', 'Nombres', 28, 16, 'Au premier mois, le quatorzième jour du mois, c\'est le sacrifice de la Pâque en l\'honneur de l\'Éternel.',
 'LSG'),
('Nombres 28:18', 'Nombres', 28, 18, 'Le premier jour, vous aurez une sainte convocation ; vous ne ferez aucun ouvrage servile.',
 'LSG'),
('Nombres 28:25', 'Nombres', 28, 25, 'Le septième jour, vous aurez une sainte convocation ; vous ne ferez aucun ouvrage servile.',
 'LSG'),
('Nombres 28:26', 'Nombres', 28, 26, 'Le jour des prémices, quand vous offrirez à l\'Éternel une nouvelle oblation lors de vos semaines, vous aurez une sainte convocation ; vous ne ferez aucun ouvrage servile.',
 'LSG'),
('Nombres 29:7',  'Nombres', 29, 7,  'Le dixième jour de ce septième mois, vous aurez une sainte convocation ; vous vous humilierez, et vous ne ferez aucun ouvrage.',
 'LSG');

-- ============================================================
-- DEUTÉRONOME
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Deutéronome 10:4', 'Deutéronome', 10, 4, 'L\'Éternel écrivit sur les tables les mêmes paroles que les dix commandements, qu\'il vous avait adressés sur la montagne du milieu du feu, le jour de l\'assemblée ; et l\'Éternel me les donna.',
 'LSG');

-- ============================================================
-- JOB
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Job 14:12', 'Job', 14, 12, 'Ainsi l\'homme se couche et ne se relève plus ; avant que les cieux soient dissous, il ne se réveillera pas, il ne sera pas réveillé de son sommeil.',
 'LSG'),
('Job 27:3',  'Job', 27, 3,  'Aussi longtemps que j\'aurai ma respiration, et que l\'esprit de Dieu sera dans mes narines,',
 'LSG');

-- ============================================================
-- PSAUMES
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Psaumes 19:1',  'Psaumes', 19, 1,  'Les cieux racontent la gloire de Dieu, et l\'étendue manifeste l\'œuvre de ses mains.',
 'LSG'),
('Psaumes 89:3',  'Psaumes', 89, 3,  'J\'ai fait une alliance avec mon élu, j\'ai juré à David, mon serviteur :',
 'LSG'),
('Psaumes 89:27', 'Psaumes', 89, 27, 'Je ferai de lui mon premier-né, le plus élevé des rois de la terre.',
 'LSG'),
('Psaumes 119:11','Psaumes', 119, 11,'Je serre ta parole dans mon cœur, afin de ne pas pécher contre toi.',
 'LSG');

-- ============================================================
-- ECCLÉSIASTE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Ecclésiaste 9:5',  'Ecclésiaste', 9, 5,  'Car les vivants savent qu\'ils mourront ; mais les morts ne savent rien, et il n\'y a plus pour eux de salaire, puisque leur mémoire est oubliée.',
 'LSG'),
('Ecclésiaste 9:6',  'Ecclésiaste', 9, 6,  'Leur amour, leur haine et leur jalousie ont déjà disparu ; et ils n\'ont plus aucune part à tout ce qui se fait sous le soleil.',
 'LSG'),
('Ecclésiaste 12:13','Ecclésiaste', 12, 13,'Écoutons la fin du discours : crains Dieu et observe ses commandements. C\'est là ce que doit faire tout homme.',
 'LSG'),
('Ecclésiaste 12:14','Ecclésiaste', 12, 14,'Car Dieu soumettra toute œuvre au jugement, au jugement qui atteint tout ce qui est caché, soit bien, soit mal.',
 'LSG');

-- ============================================================
-- ÉSAÏE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Ésaïe 9:6',   'Ésaïe', 9, 6,  'Car un enfant nous est né, un fils nous a été donné, et la domination reposera sur son épaule ; on l\'appellera Admirable, Conseiller, Dieu puissant, Père éternel, Prince de la paix.',
 'LSG'),
('Ésaïe 14:12', 'Ésaïe', 14, 12,'Comment es-tu tombé du ciel, Astre brillant, fils de l\'aurore ? Comment as-tu été précipité à terre, toi qui foulais les nations ?',
 'LSG'),
('Ésaïe 14:13', 'Ésaïe', 14, 13,'Tu disais en ton cœur : Je monterai au ciel, j\'élèverai mon trône au-dessus des étoiles de Dieu ; je m\'assiérai sur la montagne de l\'assemblée, aux flancs du septentrion ;',
 'LSG'),
('Ésaïe 14:14', 'Ésaïe', 14, 14,'je monterai sur le sommet des nues, je serai semblable au Très-Haut.',
 'LSG'),
('Ésaïe 28:9',  'Ésaïe', 28, 9,  'À qui veut-il enseigner la science ? à qui veut-il expliquer la doctrine ? à des enfants sevrés du lait, à des enfants tirés de la mamelle ?',
 'LSG'),
('Ésaïe 28:10', 'Ésaïe', 28, 10, 'Car il faut précepte sur précepte, précepte sur précepte, règle sur règle, règle sur règle, un peu ici, un peu là.',
 'LSG'),
('Ésaïe 44:21', 'Ésaïe', 44, 21, 'Souviens-toi de ceci, Jacob, et toi, Israël, car tu es mon serviteur : je t\'ai formé, tu es mon serviteur, Israël, je ne t\'oublierai pas.',
 'LSG'),
('Ésaïe 44:22', 'Ésaïe', 44, 22, 'J\'efface tes transgressions comme un nuage, et tes péchés comme une nuée : reviens à moi, car je t\'ai racheté.',
 'LSG'),
('Ésaïe 53:6',  'Ésaïe', 53, 6,  'Nous étions tous errants comme des brebis, chacun suivait sa propre voie ; et l\'Éternel a fait retomber sur lui l\'iniquité de nous tous.',
 'LSG'),
('Ésaïe 53:7',  'Ésaïe', 53, 7,  'Il a été maltraité et opprimé, et il n\'a point ouvert la bouche, semblable à un agneau qu\'on mène à la boucherie, à une brebis muette devant ceux qui la tondent ; il n\'a point ouvert la bouche.',
 'LSG'),
('Ésaïe 56:6',  'Ésaïe', 56, 6,  'Et les étrangers qui s\'attachent à l\'Éternel pour le servir et pour aimer le nom de l\'Éternel, et pour être ses serviteurs, tous ceux qui observent le sabbat sans le profaner, et qui s\'attachent à mon alliance,',
 'LSG'),
('Ésaïe 56:7',  'Ésaïe', 56, 7,  'je les amènerai sur ma sainte montagne, et je les réjouirai dans ma maison de prière ; leurs holocaustes et leurs sacrifices seront agréés sur mon autel : car ma maison sera appelée une maison de prière pour tous les peuples.',
 'LSG'),
('Ésaïe 58:13', 'Ésaïe', 58, 13, 'Si tu retiens ton pied pendant le sabbat, pour ne pas te livrer à tes affaires en mon saint jour, si tu fais du sabbat tes délices, si tu honores l\'Éternel en ne suivant pas tes voies, en ne te livrant pas à tes affaires et en ne parlant pas de vaines paroles,',
 'LSG'),
('Ésaïe 58:14', 'Ésaïe', 58, 14, 'alors tu trouveras tes délices en l\'Éternel, et je te ferai monter sur les hauteurs du pays, je te ferai manger de l\'héritage de Jacob, ton père ; car la bouche de l\'Éternel a parlé.',
 'LSG'),
('Ésaïe 66:22', 'Ésaïe', 66, 22, 'Car, comme les nouveaux cieux et la nouvelle terre que je vais faire subsisteront devant moi, dit l\'Éternel, ainsi subsistera votre race et votre nom.',
 'LSG'),
('Ésaïe 66:23', 'Ésaïe', 66, 23, 'Et, d\'une nouvelle lune à l\'autre, et d\'un sabbat à l\'autre, toute chair viendra se prosterner devant moi, dit l\'Éternel.',
 'LSG');

-- ============================================================
-- MICHÉE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Michée 5:2',  'Michée', 5, 2,  'Et toi, Bethléhem Éphrata, petit entre les milliers de Juda, de toi sortira pour moi celui qui dominera sur Israël, et dont les origines remontent aux temps anciens, aux jours de l\'éternité.',
 'LSG'),
('Michée 7:18', 'Michée', 7, 18, 'Quel Dieu est semblable à toi, qui pardonnes l\'iniquité, qui oublies les péchés du reste de ton héritage ? Il ne garde pas sa colère à toujours, car il prend plaisir à la grâce.',
 'LSG'),
('Michée 7:19', 'Michée', 7, 19, 'Il aura encore compassion de nous, il mettra sous ses pieds nos iniquités, tu jetteras dans les profondeurs de la mer tous leurs péchés.',
 'LSG');

-- ============================================================
-- ÉZÉCHIEL
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Ézéchiel 9:4',  'Ézéchiel', 9, 4,  'L\'Éternel lui dit : Passe au milieu de la ville, au milieu de Jérusalem, et fais une marque sur le front des hommes qui soupirent et qui gémissent à cause de toutes les abominations qui s\'y commettent.',
 'LSG'),
('Ézéchiel 9:5',  'Ézéchiel', 9, 5,  'Il dit aux autres, en les écoutant : Passez après lui dans la ville, et frappez ; que votre œil soit sans pitié, n\'ayez pas de miséricorde !',
 'LSG'),
('Ézéchiel 9:6',  'Ézéchiel', 9, 6,  'Tuez et exterminezles vieillards, les jeunes gens, les vierges, les enfants et les femmes ; mais n\'approchez pas de quiconque aura la marque ; vous commencerez par mon sanctuaire. Et ils commencèrent par les anciens qui étaient devant la maison.',
 'LSG'),
('Ézéchiel 18:20','Ézéchiel', 18, 20,'L\'âme qui pèche, c\'est celle qui mourra. Le fils ne portera pas l\'iniquité du père, et le père ne portera pas l\'iniquité du fils. La justice du juste sera sur lui, et la méchanceté du méchant sera sur lui.',
 'LSG'),
('Ézéchiel 20:20','Ézéchiel', 20, 20,'Sanctifiez mes sabbats, et qu\'ils soient un signe entre moi et vous, afin qu\'on sache que moi, l\'Éternel, je suis votre Dieu.',
 'LSG'),
('Ézéchiel 28:14','Ézéchiel', 28, 14,'Tu étais le chérubin protecteur, aux ailes déployées ; je t\'avais placé, tu étais sur la sainte montagne de Dieu, tu marchais au milieu des pierres brillantes comme le feu.',
 'LSG');

-- ============================================================
-- MATTHIEU
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Matthieu 1:23',  'Matthieu', 1, 23,  'La vierge sera enceinte, et elle enfantera un fils, et on lui donnera le nom d\'Emmanuel, ce qui se traduit : Dieu avec nous.',
 'LSG'),
('Matthieu 3:16',  'Matthieu', 3, 16,  'Après avoir été baptisé, Jésus sortit aussitôt de l\'eau. Et voici, les cieux s\'ouvrirent, et il vit l\'Esprit de Dieu descendre comme une colombe et venir sur lui.',
 'LSG'),
('Matthieu 3:17',  'Matthieu', 3, 17,  'Et aussitôt une voix fit entendre des cieux ces paroles : Celui-ci est mon Fils bien-aimé, en qui j\'ai mis toute mon affection.',
 'LSG'),
('Matthieu 4:10',  'Matthieu', 4, 10,  'Jésus lui dit : Retire-toi, Satan ! Car il est écrit : Tu adoreras le Seigneur, ton Dieu, et tu le serviras lui seul.',
 'LSG'),
('Matthieu 5:17',  'Matthieu', 5, 17,  'Ne croyez pas que je sois venu pour abolir la loi ou les prophètes ; je suis venu non pour abolir, mais pour accomplir.',
 'LSG'),
('Matthieu 5:18',  'Matthieu', 5, 18,  'Car, je vous le dis en vérité, tant que le ciel et la terre ne passeront point, il ne disparaîtra pas de la loi un seul iota ou un seul trait de lettre, jusqu\'à ce que tout soit arrivé.',
 'LSG'),
('Matthieu 5:44',  'Matthieu', 5, 44,  'Mais moi, je vous dis : Aimez vos ennemis, bénissez ceux qui vous maudissent, faites du bien à ceux qui vous haïssent, et priez pour ceux qui vous maltraitent et qui vous persécutent,',
 'LSG'),
('Matthieu 5:45',  'Matthieu', 5, 45,  'afin que vous soyez fils de votre Père qui est dans les cieux ; car il fait lever son soleil sur les méchants et sur les bons, et il fait pleuvoir sur les justes et sur les injustes.',
 'LSG'),
('Matthieu 7:16',  'Matthieu', 7, 16,  'Vous les reconnaîtrez à leurs fruits. Cueille-t-on des raisins sur des épines, ou des figues sur des chardons ?',
 'LSG'),
('Matthieu 12:31', 'Matthieu', 12, 31, 'C\'est pourquoi je vous dis : tout péché et tout blasphème seront pardonnés aux hommes, mais le blasphème contre l\'Esprit ne sera point pardonné.',
 'LSG'),
('Matthieu 14:14', 'Matthieu', 14, 14, 'Quand Jésus sortit, il vit une grande foule, et il fut ému de compassion pour elle, et il guérit leurs malades.',
 'LSG'),
('Matthieu 17:22', 'Matthieu', 17, 22, 'Comme ils se trouvaient rassemblés en Galilée, Jésus leur dit : Le Fils de l\'homme doit être livré entre les mains des hommes ;',
 'LSG'),
('Matthieu 17:23', 'Matthieu', 17, 23, 'ils le feront mourir, et il ressuscitera le troisième jour. Et ils furent profondément attristés.',
 'LSG'),
('Matthieu 26:41', 'Matthieu', 26, 41, 'Veillez et priez, afin que vous ne tombiez pas dans la tentation ; l\'esprit est bien disposé, mais la chair est faible.',
 'LSG'),
('Matthieu 28:19', 'Matthieu', 28, 19, 'Allez, faites de toutes les nations des disciples, les baptisant au nom du Père, du Fils et du Saint-Esprit,',
 'LSG'),
('Matthieu 28:20', 'Matthieu', 28, 20, 'et enseignez-leur à observer tout ce que je vous ai prescrit. Et voici, je suis avec vous tous les jours, jusqu\'à la fin du monde.',
 'LSG');

-- ============================================================
-- MARC
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Marc 2:27',  'Marc', 2, 27,  'Il leur dit : Le sabbat a été fait pour l\'homme, et non l\'homme pour le sabbat.',
 'LSG'),
('Marc 10:6',  'Marc', 10, 6,  'Mais au commencement de la création, Dieu les fit homme et femme.',
 'LSG');

-- ============================================================
-- LUC
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Luc 4:16',   'Luc', 4, 16,  'Il se rendit à Nazareth, où il avait été élevé, et, selon sa coutume, il entra dans la synagogue le jour du sabbat. Il se leva pour faire la lecture,',
 'LSG'),
('Luc 8:21',   'Luc', 8, 21,  'Et il leur répondit : Ma mère et mes frères, ce sont ceux qui entendent la parole de Dieu et qui la mettent en pratique.',
 'LSG'),
('Luc 10:18',  'Luc', 10, 18, 'Il leur dit : Je voyais Satan tomber du ciel comme un éclair.',
 'LSG'),
('Luc 23:55',  'Luc', 23, 55, 'Les femmes qui étaient venues de Galilée avec Jésus suivirent, et elles regardèrent le sépulcre et comment le corps avait été placé.',
 'LSG'),
('Luc 23:56',  'Luc', 23, 56, 'Étant retournées, elles préparèrent des aromates et des parfums. Et le sabbat, elles se reposèrent selon le commandement.',
 'LSG');

-- ============================================================
-- JEAN
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Jean 1:1',   'Jean', 1, 1,  'Au commencement était la Parole, et la Parole était avec Dieu, et la Parole était Dieu.',
 'LSG'),
('Jean 1:2',   'Jean', 1, 2,  'Elle était au commencement avec Dieu.',
 'LSG'),
('Jean 1:3',   'Jean', 1, 3,  'Toutes choses ont été faites par elle, et rien de ce qui a été fait n\'a été fait sans elle.',
 'LSG'),
('Jean 1:18',  'Jean', 1, 18, 'Personne n\'a jamais vu Dieu ; le Fils unique, qui est dans le sein du Père, est celui qui l\'a fait connaître.',
 'LSG'),
('Jean 5:39',  'Jean', 5, 39, 'Vous sondez les Écritures, parce que vous pensez avoir en elles la vie éternelle : ce sont elles qui rendent témoignage de moi.',
 'LSG'),
('Jean 11:11', 'Jean', 11, 11,'Après avoir dit cela, il leur dit : Notre ami Lazare est endormi ; mais je vais le réveiller.',
 'LSG'),
('Jean 13:34', 'Jean', 13, 34,'Je vous donne un commandement nouveau : Aimez-vous les uns les autres ; comme je vous ai aimés, vous aussi, aimez-vous les uns les autres.',
 'LSG'),
('Jean 13:35', 'Jean', 13, 35,'À ceci tous connaîtront que vous êtes mes disciples, si vous avez de l\'amour les uns pour les autres.',
 'LSG'),
('Jean 14:16', 'Jean', 14, 16,'Et moi, je prierai le Père, et il vous donnera un autre consolateur, afin qu\'il demeure éternellement avec vous,',
 'LSG'),
('Jean 14:26', 'Jean', 14, 26,'Mais le consolateur, l\'Esprit-Saint, que le Père enverra en mon nom, vous enseignera toutes choses, et vous rappellera tout ce que je vous ai dit.',
 'LSG'),
('Jean 16:8',  'Jean', 16, 8,  'Et quand il sera venu, il convaincra le monde en ce qui concerne le péché, la justice et le jugement.',
 'LSG'),
('Jean 16:13', 'Jean', 16, 13, 'Quand le consolateur sera venu, l\'Esprit de vérité, il vous conduira dans toute la vérité ; car il ne parlera pas de lui-même, mais il dira tout ce qu\'il aura entendu, et il vous annoncera les choses à venir.',
 'LSG'),
('Jean 17:4',  'Jean', 17, 4,  'Je t\'ai glorifié sur la terre, j\'ai accompli l\'œuvre que tu m\'as donnée à faire.',
 'LSG'),
('Jean 17:5',  'Jean', 17, 5,  'Et maintenant toi, Père, glorifie-moi auprès de toi-même de la gloire que j\'avais auprès de toi avant que le monde fût.',
 'LSG'),
('Jean 17:20', 'Jean', 17, 20, 'Ce n\'est pas pour eux seulement que je prie, mais encore pour ceux qui croiront en moi par leur parole,',
 'LSG'),
('Jean 17:21', 'Jean', 17, 21, 'afin que tous soient un, comme toi, Père, tu es en moi, et comme je suis en toi, afin qu\'eux aussi soient un en nous, pour que le monde croie que tu m\'as envoyé.',
 'LSG'),
('Jean 19:30', 'Jean', 19, 30, 'Quand Jésus eut pris le vinaigre, il dit : Tout est accompli. Et, baissant la tête, il rendit l\'esprit.',
 'LSG'),
('Jean 20:19', 'Jean', 20, 19, 'Le soir de ce même jour, qui était le premier de la semaine, les portes du lieu où se trouvaient les disciples étant fermées, par crainte des Juifs, Jésus vint, et, se plaçant au milieu d\'eux, il leur dit : La paix soit avec vous !',
 'LSG');

-- ============================================================
-- ACTES
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Actes 1:8',   'Actes', 1, 8,  'Mais vous recevrez une puissance, le Saint-Esprit survenant sur vous, et vous serez mes témoins à Jérusalem, dans toute la Judée, dans la Samarie, et jusqu\'aux extrémités de la terre.',
 'LSG'),
('Actes 2:42',  'Actes', 2, 42, 'Ils persévéraient dans l\'enseignement des apôtres, dans la communion fraternelle, dans la fraction du pain, et dans les prières.',
 'LSG'),
('Actes 3:19',  'Actes', 3, 19, 'Repentez-vous donc et convertissez-vous, pour que vos péchés soient effacés,',
 'LSG'),
('Actes 4:9',   'Actes', 4, 9,  'Si nous sommes jugés aujourd\'hui pour avoir fait du bien à un infirme, et si l\'on nous demande comment cet homme a été guéri,',
 'LSG'),
('Actes 4:10',  'Actes', 4, 10, 'sachez tous, vous et tout le peuple d\'Israël, que c\'est par le nom de Jésus-Christ de Nazareth, que vous avez crucifié, et que Dieu a ressuscité des morts, c\'est par lui que cet homme se trouve sain devant vous.',
 'LSG'),
('Actes 4:11',  'Actes', 4, 11, 'Jésus est la pierre rejetée par vous qui bâtissez, et qui est devenue la principale de l\'angle.',
 'LSG'),
('Actes 4:12',  'Actes', 4, 12, 'Il n\'y a de salut en aucun autre ; car il n\'y a sous le ciel aucun autre nom qui ait été donné parmi les hommes, par lequel nous devions être sauvés.',
 'LSG'),
('Actes 5:3',   'Actes', 5, 3,  'Pierre lui dit : Ananias, pourquoi Satan a-t-il rempli ton cœur, au point que tu aies menti au Saint-Esprit et que tu aies retenu une partie du prix du champ ?',
 'LSG'),
('Actes 5:4',   'Actes', 5, 4,  'N\'était-il pas à toi si tu le gardais ? et après la vente, n\'étais-tu pas maître du prix ? Comment as-tu eu dans le cœur d\'agir ainsi ? Ce n\'est pas à des hommes que tu as menti, c\'est à Dieu.',
 'LSG'),
('Actes 13:14', 'Actes', 13, 14,'Quittant Perge, ils se rendirent à Antioche de Pisidie ; et, étant entrés dans la synagogue le jour du sabbat, ils s\'assirent.',
 'LSG'),
('Actes 13:32', 'Actes', 13, 32,'Et nous, nous vous annonçons cette bonne nouvelle que la promesse faite à nos pères,',
 'LSG'),
('Actes 13:33', 'Actes', 13, 33,'Dieu l\'a accomplie pour nous, leurs enfants, en ressuscitant Jésus, selon ce qui est écrit au psaume second : Tu es mon Fils, je t\'ai engendré aujourd\'hui.',
 'LSG'),
('Actes 14:15', 'Actes', 14, 15,'et disant : Hommes, pourquoi faites-vous ces choses ? Nous aussi, nous sommes des hommes de même nature que vous, et nous vous annonçons que vous avez à vous convertir de ces vaines idoles au Dieu vivant, qui a fait le ciel, la terre, la mer et tout ce qui s\'y trouve.',
 'LSG'),
('Actes 16:30', 'Actes', 16, 30,'Puis il les fit sortir, et dit : Seigneurs, que faut-il que je fasse pour être sauvé ?',
 'LSG'),
('Actes 16:31', 'Actes', 16, 31,'Ils répondirent : Crois au Seigneur Jésus, et tu seras sauvé, toi et ta famille.',
 'LSG'),
('Actes 17:11', 'Actes', 17, 11,'Ces Juifs avaient des sentiments plus nobles que ceux de Thessalonique ; ils reçurent la parole avec beaucoup d\'empressement, et ils examinaient chaque jour les Écritures, pour voir si ce qu\'on leur disait était exact.',
 'LSG'),
('Actes 18:4',  'Actes', 18, 4,  'Il raisonnait dans la synagogue chaque sabbat, et il cherchait à persuader des Juifs et des Grecs.',
 'LSG'),
('Actes 20:7',  'Actes', 20, 7,  'Le premier jour de la semaine, nous étions réunis pour rompre le pain. Paul, qui devait partir le lendemain, s\'entretint avec les frères, et il prolongea son discours jusqu\'à minuit.',
 'LSG'),
('Actes 20:9',  'Actes', 20, 9,  'Un jeune homme, nommé Eutychus, qui était assis sur le bord de la fenêtre, s\'endormit d\'un profond sommeil, tandis que Paul parlait si longtemps ; vaincu par le sommeil, il tomba du troisième étage, et fut relevé mort.',
 'LSG');

-- ============================================================
-- ROMAINS
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Romains 1:20', 'Romains', 1, 20, 'En effet, les perfections invisibles de Dieu, sa puissance éternelle et sa divinité, se voient comme à l\'œil, depuis la création du monde, quand on les considère dans ses ouvrages. Ils sont donc inexcusables,',
 'LSG'),
('Romains 1:22', 'Romains', 1, 22, 'Se vantant d\'être sages, ils sont devenus fous ;',
 'LSG'),
('Romains 2:13', 'Romains', 2, 13, 'Car ce ne sont pas ceux qui écoutent la loi qui sont justes devant Dieu, mais ce sont ceux qui pratiquent la loi qui seront justifiés.',
 'LSG'),
('Romains 3:19', 'Romains', 3, 19, 'Or, nous savons que tout ce que dit la loi, elle le dit à ceux qui sont sous la loi, afin que toute bouche soit fermée, et que tout le monde soit reconnu coupable devant Dieu.',
 'LSG'),
('Romains 3:20', 'Romains', 3, 20, 'Car nulle chair ne sera justifiée devant lui par les œuvres de la loi, parce que c\'est par la loi que vient la connaissance du péché.',
 'LSG'),
('Romains 3:24', 'Romains', 3, 24, 'et ils sont gratuitement justifiés par sa grâce, par le moyen de la rédemption qui est en Jésus-Christ.',
 'LSG'),
('Romains 3:31', 'Romains', 3, 31, 'Détruisons-nous donc la loi par la foi ? Loin de là ! Au contraire, nous confirmons la loi.',
 'LSG'),
('Romains 4:2',  'Romains', 4, 2,  'Si Abraham a été justifié par les œuvres, il a sujet de se glorifier, mais non devant Dieu.',
 'LSG'),
('Romains 4:3',  'Romains', 4, 3,  'Que dit en effet l\'Écriture ? Abraham crut à Dieu, et cela lui fut imputé à justice.',
 'LSG'),
('Romains 4:4',  'Romains', 4, 4,  'Or, à celui qui fait des œuvres, le salaire est imputé, non comme une grâce, mais comme ce qui est dû.',
 'LSG'),
('Romains 4:5',  'Romains', 4, 5,  'A celui qui ne fait point d\'œuvres, mais qui croit en celui qui justifie l\'impie, sa foi lui est imputée à justice.',
 'LSG'),
('Romains 5:18', 'Romains', 5, 18, 'Ainsi donc, comme un seul péché a conduit tous les hommes à la condamnation, de même un seul acte de justice conduit tous les hommes à la justification qui donne la vie.',
 'LSG'),
('Romains 7:7',  'Romains', 7, 7,  'Qu\'est-ce à dire ? La loi est-elle péché ? Loin de là ! Mais je n\'ai connu le péché que par la loi. Car je n\'aurais pas connu la convoitise, si la loi n\'avait dit : Tu ne convoiteras point.',
 'LSG'),
('Romains 8:14', 'Romains', 8, 14, 'Car tous ceux qui sont conduits par l\'Esprit de Dieu sont fils de Dieu.',
 'LSG'),
('Romains 8:15', 'Romains', 8, 15, 'Et vous n\'avez pas reçu un esprit de servitude, pour être encore dans la crainte ; mais vous avez reçu un Esprit d\'adoption, par lequel nous crions : Abba ! Père !',
 'LSG'),
('Romains 8:16', 'Romains', 8, 16, 'L\'Esprit lui-même rend témoignage à notre esprit que nous sommes enfants de Dieu.',
 'LSG'),
('Romains 8:17', 'Romains', 8, 17, 'Or, si nous sommes enfants, nous sommes aussi héritiers : héritiers de Dieu, et cohéritiers de Christ, si toutefois nous souffrons avec lui, afin d\'être glorifiés avec lui.',
 'LSG'),
('Romains 8:26', 'Romains', 8, 26, 'De même aussi l\'Esprit nous aide dans notre faiblesse, car nous ne savons pas ce qu\'il nous convient de demander dans nos prières. Mais l\'Esprit lui-même intercède par des soupirs inexprimables ;',
 'LSG'),
('Romains 15:30','Romains', 15, 30,'Je vous exhorte, frères, par notre Seigneur Jésus-Christ et par l\'amour de l\'Esprit, à combattre avec moi dans vos prières à Dieu pour moi,',
 'LSG');

-- ============================================================
-- 1 CORINTHIENS
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('1 Corinthiens 2:10', '1 Corinthiens', 2, 10, 'Mais c\'est à nous que Dieu les a révélées par l\'Esprit. Car l\'Esprit sonde tout, même les profondeurs de Dieu.',
 'LSG'),
('1 Corinthiens 2:11', '1 Corinthiens', 2, 11, 'Qui donc, parmi les hommes, sait ce qui concerne l\'homme, si ce n\'est l\'esprit de l\'homme qui est en lui ? De même, ce qui concerne Dieu, personne ne le connaît, sinon l\'Esprit de Dieu.',
 'LSG'),
('1 Corinthiens 5:7',  '1 Corinthiens', 5, 7,  'Purifiez-vous du vieux levain, afin que vous soyez une nouvelle pâte, comme vous êtes sans levain ; car Christ, notre Pâque, a été immolé.',
 'LSG'),
('1 Corinthiens 15:3', '1 Corinthiens', 15, 3, 'Je vous ai enseigné avant tout, comme je l\'avais aussi reçu, que Christ est mort pour nos péchés, selon les Écritures ;',
 'LSG'),
('1 Corinthiens 15:4', '1 Corinthiens', 15, 4, 'qu\'il a été enseveli, et qu\'il est ressuscité le troisième jour, selon les Écritures ;',
 'LSG'),
('1 Corinthiens 15:5', '1 Corinthiens', 15, 5, 'et qu\'il est apparu à Céphas, puis aux douze.',
 'LSG'),
('1 Corinthiens 15:6', '1 Corinthiens', 15, 6, 'Ensuite, il est apparu à plus de cinq cents frères à la fois, dont la plupart sont encore vivants, et dont quelques-uns sont morts.',
 'LSG'),
('1 Corinthiens 15:7', '1 Corinthiens', 15, 7, 'Ensuite, il est apparu à Jacques, puis à tous les apôtres.',
 'LSG'),
('1 Corinthiens 15:8', '1 Corinthiens', 15, 8, 'Après eux tous, il m\'est aussi apparu à moi, comme à l\'avorton.',
 'LSG'),
('1 Corinthiens 15:11','1 Corinthiens', 15, 11,'Ainsi donc, eux ou moi, voilà ce que nous prêchons, et voilà ce que vous avez cru.',
 'LSG'),
('1 Corinthiens 15:13','1 Corinthiens', 15, 13,'Si les morts ne ressuscitent pas, Christ non plus n\'est pas ressuscité.',
 'LSG'),
('1 Corinthiens 15:17','1 Corinthiens', 15, 17,'Et si Christ n\'est pas ressuscité, votre foi est vaine, vous êtes encore dans vos péchés.',
 'LSG'),
('1 Corinthiens 15:18','1 Corinthiens', 15, 18,'Et alors aussi ceux qui sont morts en Christ sont perdus.',
 'LSG'),
('1 Corinthiens 15:20','1 Corinthiens', 15, 20,'Mais maintenant, Christ est ressuscité des morts, il est les prémices de ceux qui sont morts.',
 'LSG'),
('1 Corinthiens 15:23','1 Corinthiens', 15, 23,'Mais chacun en son rang : Christ comme prémices, puis ceux qui appartiennent à Christ, lors de son avènement.',
 'LSG'),
('1 Corinthiens 15:51','1 Corinthiens', 15, 51,'Voici, je vous dis un mystère : nous ne mourrons pas tous, mais tous nous serons changés,',
 'LSG'),
('1 Corinthiens 15:52','1 Corinthiens', 15, 52,'en un instant, en un clin d\'œil, à la dernière trompette. La trompette sonnera, et les morts ressusciteront incorruptibles, et nous, nous serons changés.',
 'LSG'),
('1 Corinthiens 15:53','1 Corinthiens', 15, 53,'Car il faut que ce corps corruptible revête l\'incorruptibilité, et que ce corps mortel revête l\'immortalité.',
 'LSG'),
('1 Corinthiens 16:1', '1 Corinthiens', 16, 1, 'Quant à la collecte en faveur des saints, agissez, vous aussi, comme je l\'ai ordonné aux Églises de la Galatie.',
 'LSG'),
('1 Corinthiens 16:2', '1 Corinthiens', 16, 2, 'Que chacun de vous, le premier jour de la semaine, mette à part chez lui ce qu\'il pourra, selon sa prospérité, afin qu\'on n\'attende pas mon arrivée pour recueillir les dons.',
 'LSG');

-- ============================================================
-- 2 CORINTHIENS
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('2 Corinthiens 13:14','2 Corinthiens', 13, 14,'Que la grâce du Seigneur Jésus-Christ, l\'amour de Dieu, et la communion du Saint-Esprit, soient avec vous tous !',
 'LSG');

-- ============================================================
-- GALATES
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Galates 4:4', 'Galates', 4, 4,  'Mais, lorsque les temps ont été accomplis, Dieu a envoyé son Fils, né d\'une femme, né sous la loi,',
 'LSG'),
('Galates 4:5', 'Galates', 4, 5,  'afin qu\'il rachetât ceux qui étaient sous la loi, et que nous reçussions l\'adoption.',
 'LSG'),
('Galates 5:13','Galates', 5, 13, 'Frères, vous avez été appelés à la liberté, seulement ne faites pas de cette liberté un prétexte pour la chair ; mais rendez-vous, par l\'amour, serviteurs les uns des autres.',
 'LSG');

-- ============================================================
-- ÉPHÉSIENS
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Éphésiens 2:5',  'Éphésiens', 2, 5,  'nous a rendus vivants avec Christ, alors que nous étions morts par nos fautes, — c\'est par grâce que vous êtes sauvés, —',
 'LSG'),
('Éphésiens 2:6',  'Éphésiens', 2, 6,  'nous a ressuscités ensemble, et nous a fait asseoir ensemble dans les lieux célestes, en Jésus-Christ,',
 'LSG'),
('Éphésiens 2:9',  'Éphésiens', 2, 9,  'non point par les œuvres, afin que personne ne se glorifie.',
 'LSG'),
('Éphésiens 3:14', 'Éphésiens', 3, 14, 'C\'est pourquoi je fléchis les genoux devant le Père de notre Seigneur Jésus-Christ,',
 'LSG'),
('Éphésiens 3:15', 'Éphésiens', 3, 15, 'duquel toute famille dans les cieux et sur la terre tire son nom.',
 'LSG'),
('Éphésiens 4:30', 'Éphésiens', 4, 30, 'N\'attristez pas le Saint-Esprit de Dieu, par lequel vous avez été scellés pour le jour de la rédemption.',
 'LSG'),
('Éphésiens 6:14', 'Éphésiens', 6, 14, 'Tenez donc ferme : ayez à vos reins la vérité pour ceinture, revêtez la cuirasse de la justice,',
 'LSG');

-- ============================================================
-- PHILIPPIENS
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Philippiens 2:8', 'Philippiens', 2, 8, 'et s\'étant trouvé homme quant à la condition, il s\'est humilié lui-même, se rendant obéissant jusqu\'à la mort, même jusqu\'à la mort de la croix.',
 'LSG');

-- ============================================================
-- COLOSSIENS
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Colossiens 1:16', 'Colossiens', 1, 16, 'car en lui ont été créées toutes les choses qui sont dans les cieux et sur la terre, les visibles et les invisibles, trônes, dignités, dominations, autorités. Tout a été créé par lui et pour lui.',
 'LSG'),
('Colossiens 2:16', 'Colossiens', 2, 16, 'Que personne donc ne vous juge au sujet du manger ou du boire, ou au sujet d\'une fête, d\'une nouvelle lune, ou des sabbats :',
 'LSG'),
('Colossiens 2:17', 'Colossiens', 2, 17, 'c\'était l\'ombre des choses à venir, mais le corps est en Christ.',
 'LSG');

-- ============================================================
-- 1 TIMOTHÉE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('1 Timothée 6:14','1 Timothée', 6, 14,'d\'observer le commandement, en te conservant pur et irréprochable jusqu\'à l\'apparition de notre Seigneur Jésus-Christ,',
 'LSG'),
('1 Timothée 6:16','1 Timothée', 6, 16,'le seul qui possède l\'immortalité, qui habite une lumière inaccessible, que nul homme n\'a vu ni ne peut voir, à qui appartiennent l\'honneur et la puissance éternelle. Amen !',
 'LSG');

-- ============================================================
-- 2 TIMOTHÉE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('2 Timothée 3:15','2 Timothée', 3, 15,'dès l\'enfance, tu connais les saintes lettres, qui peuvent te rendre sage à salut par la foi en Jésus-Christ.',
 'LSG'),
('2 Timothée 3:16','2 Timothée', 3, 16,'Toute Écriture est inspirée de Dieu, et utile pour enseigner, pour convaincre, pour corriger, pour instruire dans la justice,',
 'LSG');

-- ============================================================
-- TITE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Tite 3:5', 'Tite', 3, 5, 'il nous a sauvés, non à cause des œuvres de justice que nous aurions faites, mais selon sa miséricorde, par le bain de la régénération et le renouvellement du Saint-Esprit,',
 'LSG');

-- ============================================================
-- HÉBREUX
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Hébreux 1:1',  'Hébreux', 1, 1,  'Après avoir anciennement, à plusieurs reprises et de plusieurs manières, parlé à nos pères par les prophètes, Dieu,',
 'LSG'),
('Hébreux 1:2',  'Hébreux', 1, 2,  'dans ces derniers temps, nous a parlé par le Fils, qu\'il a établi héritier de toutes choses, par lequel il a aussi créé le monde,',
 'LSG'),
('Hébreux 1:3',  'Hébreux', 1, 3,  'et qui, étant le reflet de sa gloire et l\'empreinte de sa personne, et soutenant toutes choses par sa parole puissante, a fait la purification des péchés et s\'est assis à la droite de la majesté divine dans les lieux très hauts.',
 'LSG'),
('Hébreux 1:6',  'Hébreux', 1, 6,  'Et quand il introduit de nouveau le premier-né dans le monde, il dit : Que tous les anges de Dieu l\'adorent !',
 'LSG'),
('Hébreux 1:10', 'Hébreux', 1, 10, 'Et : C\'est toi, Seigneur, qui au commencement as fondé la terre, et les cieux sont l\'œuvre de tes mains ;',
 'LSG'),
('Hébreux 2:9',  'Hébreux', 2, 9,  'Mais nous voyons Jésus couronné de gloire et d\'honneur à cause de la mort qu\'il a soufferte, lui qui avait été fait un peu moindre que les anges, afin que, par la grâce de Dieu, il souffrît la mort pour tous.',
 'LSG'),
('Hébreux 2:14', 'Hébreux', 2, 14, 'Ainsi donc, puisque les enfants participent au sang et à la chair, il y a participé lui-même également, afin que, par la mort, il détruisît celui qui a la puissance de la mort, c\'est-à-dire le diable,',
 'LSG'),
('Hébreux 2:15', 'Hébreux', 2, 15, 'et qu\'il délivrât tous ceux qui, par crainte de la mort, étaient toute leur vie retenus dans la servitude.',
 'LSG'),
('Hébreux 4:10', 'Hébreux', 4, 10, 'car celui qui entre dans le repos de Dieu se repose aussi de ses œuvres, comme Dieu s\'est reposé des siennes.',
 'LSG'),
('Hébreux 4:12', 'Hébreux', 4, 12, 'Car la parole de Dieu est vivante et efficace, plus tranchante qu\'une épée quelconque à deux tranchants, pénétrante jusqu\'à partager âme et esprit, jointures et moelles ; elle juge les sentiments et les pensées du cœur.',
 'LSG'),
('Hébreux 8:10', 'Hébreux', 8, 10, 'Car voici l\'alliance que je ferai avec la maison d\'Israël, après ces jours-là, dit le Seigneur : Je mettrai mes lois dans leur entendement, je les écrirai dans leur cœur ; et je serai leur Dieu, et ils seront mon peuple.',
 'LSG'),
('Hébreux 10:25','Hébreux', 10, 25,'n\'abandonnons pas notre assemblée, comme c\'est la coutume de quelques-uns ; mais exhortons-nous mutuellement, et cela d\'autant plus que vous voyez s\'approcher le jour.',
 'LSG'),
('Hébreux 11:3', 'Hébreux', 11, 3, 'C\'est par la foi que nous comprenons que le monde a été formé par la parole de Dieu, en sorte que ce qu\'on voit n\'a pas été fait de choses visibles.',
 'LSG'),
('Hébreux 12:14','Hébreux', 12, 14,'Recherchez la paix avec tous, et la sanctification, sans laquelle personne ne verra le Seigneur.',
 'LSG');

-- ============================================================
-- JACQUES
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Jacques 2:10','Jacques', 2, 10,'Car quiconque observe toute la loi, mais pèche contre un seul commandement, devient coupable de tous.',
 'LSG'),
('Jacques 2:11','Jacques', 2, 11,'En effet, celui qui a dit : Tu ne commettras pas d\'adultère, a dit aussi : Tu ne tueras pas. Or, si tu ne commets pas d\'adultère, mais que tu tues, tu es transgresseur de la loi.',
 'LSG'),
('Jacques 2:12','Jacques', 2, 12,'Parlez et agissez comme devant être jugés par une loi de liberté.',
 'LSG');

-- ============================================================
-- 1 PIERRE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('1 Pierre 1:18','1 Pierre', 1, 18,'sachant que ce n\'est pas par des choses corruptibles, par de l\'argent ou de l\'or, que vous avez été rachetés de la vaine manière de vivre que vous aviez héritée de vos pères,',
 'LSG'),
('1 Pierre 1:19','1 Pierre', 1, 19,'mais par le sang précieux de Christ, comme d\'un agneau sans défaut et sans tache,',
 'LSG'),
('1 Pierre 5:8', '1 Pierre', 5, 8, 'Soyez sobres et veillez. Votre adversaire, le diable, rôde comme un lion rugissant, cherchant qui il dévorera.',
 'LSG');

-- ============================================================
-- 2 PIERRE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('2 Pierre 1:21','2 Pierre', 1, 21,'car ce n\'est pas par une volonté humaine qu\'une prophétie a jamais été apportée, mais c\'est poussés par le Saint-Esprit que des hommes ont parlé de la part de Dieu.',
 'LSG');

-- ============================================================
-- 1 JEAN
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('1 Jean 2:4',  '1 Jean', 2, 4,  'Celui qui dit : Je le connais, et qui ne garde pas ses commandements, est un menteur, et la vérité n\'est point en lui.',
 'LSG'),
('1 Jean 3:4',  '1 Jean', 3, 4,  'Quiconque pèche transgresse la loi, et le péché est la transgression de la loi.',
 'LSG'),
('1 Jean 3:8',  '1 Jean', 3, 8,  'Celui qui pèche est du diable, car le diable pèche dès le commencement. Le Fils de Dieu a paru pour détruire les œuvres du diable.',
 'LSG'),
('1 Jean 4:16', '1 Jean', 4, 16, 'Et nous, nous avons connu l\'amour que Dieu a pour nous, et nous y avons cru. Dieu est amour ; et celui qui demeure dans l\'amour demeure en Dieu, et Dieu demeure en lui.',
 'LSG'),
('1 Jean 4:17', '1 Jean', 4, 17, 'En ceci, l\'amour est parfait en nous, afin que nous ayons de l\'assurance au jour du jugement ; car tel il est, tels nous sommes aussi dans ce monde.',
 'LSG'),
('1 Jean 4:18', '1 Jean', 4, 18, 'La crainte n\'est pas dans l\'amour, mais l\'amour parfait bannit la crainte ; car la crainte renferme un châtiment, et celui qui craint n\'est pas parfait dans l\'amour.',
 'LSG'),
('1 Jean 5:3',  '1 Jean', 5, 3,  'Car l\'amour de Dieu consiste à garder ses commandements. Et ses commandements ne sont pas pénibles,',
 'LSG');

-- ============================================================
-- APOCALYPSE
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('Apocalypse 1:3',  'Apocalypse', 1, 3,  'Heureux celui qui lit et ceux qui entendent les paroles de cette prophétie, et qui gardent les choses qui y sont écrites ! Car le temps est proche.',
 'LSG'),
('Apocalypse 1:10', 'Apocalypse', 1, 10, 'Je fus ravi en esprit au jour du Seigneur, et j\'entendis derrière moi une voix forte, comme le son d\'une trompette,',
 'LSG'),
('Apocalypse 5:7',  'Apocalypse', 5, 7,  'Il vint, et il prit le livre de la main droite de celui qui était assis sur le trône.',
 'LSG'),
('Apocalypse 5:8',  'Apocalypse', 5, 8,  'Quand il eut pris le livre, les quatre êtres vivants et les vingt-quatre anciens se prosternèrent devant l\'agneau, ayant chacun une harpe et des coupes d\'or remplies de parfums, qui sont les prières des saints.',
 'LSG'),
('Apocalypse 5:9',  'Apocalypse', 5, 9,  'Et ils chantaient un cantique nouveau, en disant : Tu es digne de prendre le livre et d\'en ouvrir les sceaux ; car tu as été immolé, et tu as racheté pour Dieu par ton sang des hommes de toute tribu, de toute langue, de tout peuple et de toute nation ;',
 'LSG'),
('Apocalypse 7:1',  'Apocalypse', 7, 1,  'Après cela, je vis quatre anges debout aux quatre coins de la terre, retenant les quatre vents de la terre, afin qu\'il ne soufflât point de vent sur la terre, ni sur la mer, ni sur aucun arbre.',
 'LSG'),
('Apocalypse 7:2',  'Apocalypse', 7, 2,  'Et je vis un autre ange, qui montait du côté du soleil levant, et qui tenait le sceau du Dieu vivant ; il cria d\'une voix forte aux quatre anges à qui il avait été donné de faire du mal à la terre et à la mer,',
 'LSG'),
('Apocalypse 7:3',  'Apocalypse', 7, 3,  'en disant : Ne faites pas de mal à la terre, ni à la mer, ni aux arbres, jusqu\'à ce que nous ayons marqué du sceau le front des serviteurs de notre Dieu.',
 'LSG'),
('Apocalypse 12:10','Apocalypse', 12, 10,'Et j\'entendis dans le ciel une voix forte qui disait : Maintenant le salut est arrivé, la puissance et le règne de notre Dieu, et l\'autorité de son Christ ; car il a été précipité, l\'accusateur de nos frères, celui qui les accusait devant notre Dieu jour et nuit.',
 'LSG'),
('Apocalypse 14:9', 'Apocalypse', 14, 9, 'Et un troisième ange les suivit, en disant d\'une voix forte : Si quelqu\'un adore la bête et son image, et reçoit une marque sur son front ou sur sa main,',
 'LSG'),
('Apocalypse 14:10','Apocalypse', 14, 10,'il boira, lui aussi, du vin de la colère de Dieu, versé sans mélange dans la coupe de sa colère, et il sera tourmenté dans le feu et dans le soufre, devant les saints anges et devant l\'agneau.',
 'LSG'),
('Apocalypse 17:3', 'Apocalypse', 17, 3, 'Il me transporta en esprit dans un désert. Et je vis une femme assise sur une bête écarlate, pleine de noms de blasphème, ayant sept têtes et dix cornes.',
 'LSG'),
('Apocalypse 18:4', 'Apocalypse', 18, 4, 'Et j\'entendis une autre voix du ciel, qui disait : Sortez de la Babylone, mon peuple, afin que vous ne participiez point à ses péchés, et que vous n\'ayez point de part à ses fléaux.',
 'LSG');

-- ============================================================
-- 2 SAMUEL
-- ============================================================
INSERT IGNORE INTO versets (reference, livre, chapitre, verset, texte, version) VALUES
('2 Samuel 23:2','2 Samuel', 23, 2,'L\'Esprit de l\'Éternel a parlé par moi, et sa parole est sur ma langue.',
 'LSG');

-- Confirmation
SELECT CONCAT('Migration terminée : ', COUNT(*), ' versets au total dans la base.') AS statut
FROM versets;
