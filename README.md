# Version courante : 1.0.2

* **v1.0.2 : Ajout d'un paramètre bMore dans la méthode aGetSelect2JSONResearch permettant de n'afficher que les n premiers éléments**

* **v1.0.1 : Ajout d'une méthode de formatage de fragments SQL pour les SET des UPDATE**
Cette méthode "sFormateChampsRequeteEdition" est utilisée par les nouvelles versions des classes de data générées 
par le générateur de la release-1.1. On lui passe un tableau champs/valeurs et elle nous retourne un fragment de 
SQL prêt à être utilisé dans le SET des INSERT ou UPDATE.