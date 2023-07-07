<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Genre de l'application admin
 */

class AdminEnchere extends Admin
{

  protected $methodes = [
    'l' => ['nom' => 'listerEncheres', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    'a' => ['nom' => 'ajouterEnchere', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]],
    'm' => ['nom' => 'modifierEnchere', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR]],
    's' => ['nom' => 'supprimerEnchere', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct()
  {
    $this->id = $_GET['id'] ?? null;
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les genres
   */
  public function listerEncheres()
  {
    $encheres = $this->oRequetesSQL->getEncheres();
    (new Vue)->generer(
      'vAdminEncheres',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre' => 'Gestion des encheres',
        'entite' => 'enchere',
        'encheres' => $encheres,
        'classRetour' => $this->classRetour,
        'messageRetourAction' => $this->messageRetourAction
      ],
      'gabarit-admin'
    );

  }

  /**
   * Ajouter un genre
   */
  public function ajouterEnchere()
  {
    if (count($_POST) !== 0) {
      $enchere = $_POST;
      $oEnchere = new Enchere($enchere);
      $erreurs = $oEnchere->erreurs;
      if (count($erreurs) === 0) {
        
        $retour = $this->oRequetesSQL->AjouterEnchere([
          'DateDebut' => $oEnchere->DateDebut,
          'DateFin' => $oEnchere->DateFin,
          'PrixPlancher' => $oEnchere->PrixPlancher,
          'UtilisateurID' => $oEnchere->UtilisateurID,
          'Visible' => $oEnchere->Visible,
          //'Status' => $oEnchere->Status,
          'ID' => $oEnchere->ID
        ]);

        $retourB = $this->oRequetesSQL->ajouterTimbre([
          'Nom' => $oEnchere->TimbreNom,
          //'DateCreation' => $oEnchere->TimbreDateCreation,
          'Couleur' => $oEnchere->TimbreCouleur,
          'PaysOrigine' => $oEnchere->TimbrePaysOrigine,
          'EtatCondition' => $oEnchere->TimbreEtatCondition,
          'Tirage' => $oEnchere->TimbreTirage,
          'Longueur' => $oEnchere->TimbreLongueur,
          'Largeur' => $oEnchere->TimbreLargeur,
          'Certifie' => $oEnchere->TimbreCertifie,
          'CategorieID' => $oEnchere->TimbreCategorieID,
          'ID' => $oEnchere->TimbreID
        ]);


        if (preg_match('/^[1-9]\d*$/', $retour)) {
          $this->messageRetourAction = "Ajout de l'enchère numéro $oEnchere->id effectué.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Ajout de l'enchère non effectué.";
        }
        $this->listerEncheres();
        exit;
      }
    } else {
      $enchere = [];
      $categories = $this->oRequetesSQL->getCategories();
      $erreurs = [];
    }

    (new Vue)->generer(
      'vAdminEnchereAjouter',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre' => "Ajouter une enchere",
        'enchere' => $enchere,
        'categories' => $categories,
        'entite' => 'enchere',
        'erreurs' => $erreurs
      ],
      'gabarit-admin'
    );
  }

  /**
   * Modifier un genre
   */
  public function modifierEnchere()
  {
    if (count($_POST) !== 0) {
      $enchere = $_POST;
      $oEnchere = new Enchere($enchere);
      $erreurs = $oEnchere->erreurs;
      if (count($erreurs) === 0) {

        $retour = $this->oRequetesSQL->modifierEnchere([
          'DateDebut' => $oEnchere->DateDebut,
          'DateFin' => $oEnchere->DateFin,
          'PrixPlancher' => $oEnchere->PrixPlancher,
          'UtilisateurID' => $oEnchere->UtilisateurID,
          'Visible' => $oEnchere->Visible,
          //'Status' => $oEnchere->Status,
          'ID' => $oEnchere->ID
        ]);

        $retourB = $this->oRequetesSQL->modifierTimbre([
          'Nom' => $oEnchere->TimbreNom,
          //'DateCreation' => $oEnchere->TimbreDateCreation,
          'Couleur' => $oEnchere->TimbreCouleur,
          'PaysOrigine' => $oEnchere->TimbrePaysOrigine,
          'EtatCondition' => $oEnchere->TimbreEtatCondition,
          'Tirage' => $oEnchere->TimbreTirage,
          'Longueur' => $oEnchere->TimbreLongueur,
          'Largeur' => $oEnchere->TimbreLargeur,
          'Certifie' => $oEnchere->TimbreCertifie,
          'CategorieID' => $oEnchere->TimbreCategorieID,
          'ID' => $oEnchere->TimbreID
        ]);
        if ($retour === true) {
          $this->messageRetourAction = "Modification de l'enchère numéro $this->id effectuée.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Modification enchère numéro $this->id non effectuée.";
        }
        $this->listerEncheres();
        exit;
      }
    } else {
      $enchere = $this->oRequetesSQL->getEnchere($this->id);
      $categories = $this->oRequetesSQL->getCategories();
      $erreurs = [];
    }

    (new Vue)->generer(
      'vAdminEnchereModifier',
      [
        'oUtilConn' => self::$oUtilConn,
        'titre' => "Modifier le enchère numéro $this->id",
        'enchere' => $enchere,
        'categories' => $categories,
        'entite' => 'enchere',
        'erreurs' => $erreurs
      ],
      'gabarit-admin'
    );
  }

  /**
   * Supprimer un genre
   */
  public function supprimerEnchere()
  {
    $retour = $this->oRequetesSQL->supprimerEnchere($this->id);
    if ($retour === false)
      $this->classRetour = "erreur";
    $this->messageRetourAction = "Suppression enchère numéro $this->id " . ($retour ? "" : "non ") . "effectuée.";
    $this->listerEncheres();
  }
}