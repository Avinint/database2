# Version courante : 1.0.9

* **v1.0.9 : Prise en charge du code d'erreur de driver spécifique pour les contraintes d'unicité en plus du SQLSTATE**
- Ajout d'un message lors d'une erreur de contrainte d'unicité et récupération du champ posant problème
- Utilisation de errorInfo() au lieu de errorCode() pour avoir plus de détails sur l'erreur

* **v1.0.8 : Ajout de la prise en compte de nouveaux champs mysql pour la génération**
- Ajout des champs double et longtext
- Mutualisation du switch qui génère les nom de champs mappés

* **v1.0.7 : Exécution du vLogRequete uniquement si la méthode existe**

* **v1.0.6 : Log automatique des requêtes SQL ayant échouées**
Nécessite un coeur en v2.4.4 minimum.

* **v1.0.5 : Ajout du tri (ORDER BY) dans les paramètres de la méthode qui effectue une requête pour dynamiser le select2**
Ajout d'un paramètre sOrderBy à la méthode 'aGetSelect2JSON', pour effectuer un tri personnaliser dans la requête qui
récupére les datas en vue de la dynamisation d'un select2. Si sOrderBy n'est pas défini, c'est la valeur 'aChamps[1]' qui est 
utilisé à la place

* **v1.0.4 : Ajout d'un paramètre bMore dans la méthode aGetSelect2JSONResearch permettant de n'afficher que les n premiers éléments**

* **v1.0.3 : Prise en compte du contexte d'exécution de la requête lors du aGetElements**
Prise en compte du paramètre szContexte lors de l'appel de la méthode szGetSelect dans aGetElements

* **v1.0.2 : Prise en compte du contexte d'exécution de la requête lors du nGetNbElements** 
Ajout d'un troisième paramètre sContexte dans la méthode nGetNbElements.

* **v1.0.1 : Ajout d'une méthode de formatage de fragments SQL pour les SET des UPDATE**
Cette méthode "sFormateChampsRequeteEdition" est utilisée par les nouvelles versions des classes de data générées 
par le générateur de la release-1.1. On lui passe un tableau champs/valeurs et elle nous retourne un fragment de 
SQL prêt à être utilisé dans le SET des INSERT ou UPDATE.