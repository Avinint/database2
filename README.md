# Version courante : 1.0.2

* **v1.0.2 : Prise en compte du contexte d'exécution de la requête lors du nGetNbElements** 
Ajout d'un troisième paramètre sContexte dans la méthode nGetNbElements.

* **v1.0.1 : Ajout d'une méthode de formatage de fragments SQL pour les SET des UPDATE**
Cette méthode "sFormateChampsRequeteEdition" est utilisée par les nouvelles versions des classes de data générées 
par le générateur de la release-1.1. On lui passe un tableau champs/valeurs et elle nous retourne un fragment de 
SQL prêt à être utilisé dans le SET des INSERT ou UPDATE.