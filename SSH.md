Processus pour mettre en place le SSH CPANEL (si plus rien ne marche): 

1) Supprimer l'intégralité des fichiers présents dans le dossier .ssh (root CPANEL)
2) Supprimer (si éxistante) la clé de déploiement présente sur GitHub (allez sur le repo concerné, puis settings et Deploy keys)

    Vous êtes désormais sur une installation complètement vierge, nous pouvons donc commencer.

3) Allez sur la page d'accueil CPANEL, puis terminal (en bas).
4) executez cette commande `ssh-keygen -t ed25519 -C "youremailaddress@domain.tld DAY-MONTH-YEAR" -f ~/.ssh/my_key`
   - Remplacer ce qu'il y a entre double guillements par ce que vous voulez, ou suivez le schéma donné (cela n'a pas d'importance, ce que vous mettrez 
    ici se placera à la fin de la clé générée, afin de les différencier).
5) Une fois cela fait, rendez vous dans Accès SSH dans la page d'accueil CPANEL, puis Gérer les clés SSH. Authoriser la clé créé à l'instant. 
6) Retour dans le terminal, Faites cette suite de commande: 
    - `touch ~/.ssh/config` (Création du fichier config)
    - `chmod 0600 ~/.ssh/config` (Changement des permissions d'accès au fichier)
    - `chown cpanelusername:cpanelusername ~/.ssh/config` (Commande pour s'assurer que vous êtes bien le créateur du fichier)
   
7) Ouvrez le fichier config grâce au file manager et mettez ceci à l'intérieur : 
    - `Host *
      IdentityFile ~/.ssh/my_key
      User remoteusername`
      Si vous avez modifié le nom de votre clé dans l'étape 4 (toute fin de la commande) remplacez "my_key" par le nom choisi en étape 4.
      Remplacez User par le nom avec lequel vous vous connectez sur la plateforme sur laquelle vous Pushez vos mises à jour (GitHub dans notre cas)
   
8) Retour dans le terminal, rentrez cette commande pour copier la clé publique créé un peu plus tôt :
    - `cat ~/.ssh/my_key.pub` (comme avant, remplacez "my_key" par le nom que vous auriez choisi à la place)
   
9) Allez dans GitHub, votre repo, puis Deploy Keys. Remplissez le nom par ce que vous voulez.
     (Par habitude je met "Vers Cpanel" pour savoir avec quoi cette clé communique), collez la clé dans le deuxième espace, puis cochez la case en dessous.
10) Rendez-vous sur la page d'accueil du repo GitHub, puis copiez le lien qui nous permet de cloner le repo en SSH (commence par git@github)
11) Rendez-vous sur la page d'accueil de CPANEL, puis Git Version Control. 
12) Créez un Repository, en collant le lien git@github dans la partie "Clone URL", puis dans Repositoty Path l'endroit où vous voulez que le repo se clone.
    (faites attention à ce que il n'y ait pas déjà un dossier du même nom présent à cette endroit, qu'il soit vide ou non)
    (Le 3eme se rempli automatiquement, pas besoin de le changer)
13) Cliquez sur Créer. 

Si jamais un problème apparait, vérifiez en priorité le nom "User" dans le fichier config, le nom de la clé créé, puis que les clés correspondent.