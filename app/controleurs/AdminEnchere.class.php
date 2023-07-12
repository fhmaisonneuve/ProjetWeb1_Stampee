<?php

/**
 * Classe Contrôleur des requêtes sur l'entité Genre de l'application admin
 */

class AdminEnchere extends Admin
{

  protected $methodes = [
    'l' => ['nom' => 'listerEncheres', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR, Utilisateur::PROFIL_CLIENT]],
    'a' => ['nom' => 'ajouterEnchere', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CLIENT]],
    'm' => ['nom' => 'modifierEnchere', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CORRECTEUR, Utilisateur::PROFIL_CLIENT]],
    's' => ['nom' => 'supprimerEnchere', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CLIENT]],
    'lu' => ['nom' => 'listerEncheresUser', 'droits' => [Utilisateur::PROFIL_ADMINISTRATEUR, Utilisateur::PROFIL_EDITEUR, Utilisateur::PROFIL_CLIENT, Utilisateur::PROFIL_CLIENT]]
  ];

  /**
   * Constructeur qui initialise des propriétés à partir du query string
   * et la propriété oRequetesSQL déclarée dans la classe Routeur
   * 
   */
  public function __construct()
  {
    $this->id = $_GET['id'] ?? null;
    self::$action = $_GET['action'] ?? 'l';
    $this->oRequetesSQL = new RequetesSQL;
  }

  /**
   * Lister les encheres liées à un utilisateur courant
   */
  public function listerEncheresUser()
  {
    $moi = self::$oUtilConn;
    $this->listerEncheres(self::$oUtilConn->utilisateur_id);
  }

  /**
   * Lister les encheres
   */
  public function listerEncheres($id = null)
  {
    $encheres = $this->oRequetesSQL->getEncheres($id);
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
   * Ajouter une enchère
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
        ]);

        $retourB = $this->oRequetesSQL->ajouterTimbre([
          'Nom' => $oEnchere->TimbreNom,
          'Couleur' => $oEnchere->TimbreCouleur,
          'PaysOrigine' => $oEnchere->TimbrePaysOrigine,
          'EtatCondition' => $oEnchere->TimbreEtatCondition,
          'Tirage' => $oEnchere->TimbreTirage,
          'Longueur' => $oEnchere->TimbreLongueur,
          'Largeur' => $oEnchere->TimbreLargeur,
          'Certifie' => $oEnchere->TimbreCertifie,
          'CategorieID' => $oEnchere->TimbreCategorieID,
          'EnchereID' => $retour
        ]);

        if (preg_match('/^[1-9]\d*$/', $retour)) {
          $this->messageRetourAction = "Ajout de l'enchère numéro $retour effectué.";
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
   * Modifier une enchere
   */
  public function modifierEnchere()
  {
    if (count($_POST) !== 0) {
      $enchere = $_POST;
      $file = $_FILES;
      $file = $_FILES['ImageCheminImage'];
      $oEnchere = new Enchere($enchere);
      $erreurs = $oEnchere->erreurs;
      if (count($erreurs) === 0) {

        $retour = $this->oRequetesSQL->modifierEnchere([
          'DateDebut' => $oEnchere->DateDebut,
          'DateFin' => $oEnchere->DateFin,
          'PrixPlancher' => $oEnchere->PrixPlancher,
          'UtilisateurID' => $oEnchere->UtilisateurID,
          'Visible' => $oEnchere->Visible,
          'ID' => $oEnchere->ID
        ]);

        $retourTimbre = $this->oRequetesSQL->modifierTimbre([
          'Nom' => $oEnchere->TimbreNom,
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

        $this->messageRetourAction = '';
        if ($retour) {
          $this->messageRetourAction .= "Modification de l'enchère numéro $this->id effectuée.";
        }
        if ($retourTimbre) {
          $this->messageRetourAction .= "Modification au timbre de l'enchère numéro $this->id effectuée.";
        }

        if ($retour === true || $retourTimbre === true) {
          $this->messageRetourAction = "Modification de l'enchère numéro $this->id effectuée.";
        } else {
          $this->classRetour = "erreur";
          $this->messageRetourAction = "Pas de changements pour enchère numéro $this->id.";
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
   * Supprimer une enchère
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