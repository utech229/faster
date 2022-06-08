
	'use strict';
		const _avatar_default         = `{{ avatar_default|default('') }}`;
		const _base_path              = `{{asset("")}}`;
		const _locale              	  = "{{ _locale|default('fr') }}";
		// Ajax Error
		const _ajaxErrorText               =  `{{ "Une erreur est survenu, veuillez réessayer plus tard"|trans }}`;

		// Connexion
		const _Email_NotEmpty_Connexion	=	`{{ "L'adresse mail est requise"|trans|raw }}`;
		const _Email_EmailAddress		=	`{{ "La valeur n'est pas une adresse e-mail valide"|trans|raw }}`;

		// Traduction dans DataTables.js
		const _language_datatables    = `{{ _language_datatables|default('') }}`;
		// Traduction dans List.js
		const _Delete                 = `{{ "Supprimer"|trans }}`;
		const _Edit                   = `{{ "Modifier"|trans }}`;
		const _Add                    = `{{ "Ajouter"|trans }}`;
		const _Details                = `{{ "Détails"|trans }}`;
		const _delete_question        = `{{ "Vous êtes sûr de vouloir supprimer ?"|trans }}`;
		const _delete_question_customer= `{{ "Êtes-vous sûr de vouloir supprimer les sélectionnés ?"|trans}}`;
		const _confirm_delete         = `{{ "Oui, supprimé!"|trans }}`;
		const _confirm_validation     = `{{ "Oui, validé!"|trans }}`;
		const _confirm_success        = `{{ "Oui, d'accord!"|trans|raw }}`;
		const _confirm_cancel         = `{{ "Non, annulé!"|trans }}`;
		const _delete_info            = `{{ "Vous avez supprimé"|trans }}`;
		const _delete_info_customer   = `{{ "Vous avez supprimé toutes les sélections"|trans }}`;
		const _not_delete             = `{{ "n'a pas été supprimé."|trans|raw }}`;
		const _not_delete_customer    = `{{ "Les sélectionnés n'ont pas été supprimés."|trans|raw }}`;
		// Traduction dans Export.js et List.js
		const _Date_notEmpty          = `{{ "Une plage de dates est obligatoiree"|trans }}`;
		
		///Event traduction on javascript
		const _Event_Add_Name_Validation             = `{{ "Nom de l'événement obligatoire"|trans|raw }}`;
		const _min_lenght             = `{{ "Cette valeur doit comporter au moins 3 caractères"|trans }}`;
		const _Category_Add_Name_Validation      = `{{ "Nom de la catégorie obligatoire"|trans }}`;
		const _short_description_Validation             = `{{ "Une courte description est obligatoire"|trans }}`;
		const _Form_Error_Swal_Notification          = `{{ "Désolé, il semble que un ou plusieurs champs obligatoires sont incorrects, veuillez bien renseigner."|trans }}`;
		const _Form_Ok_Swal_Button_Text_Notification = `{{ "Ok, j'ai compris."|trans|raw }}`;
		const _Form_click_Swal_Button_Text_Notification = `{{ "Cliquez ici."|trans|raw }}`;
		const _Swal_success = `{{ "Succès"|trans }}`;
		const _Swal_error = `{{ "Erreur"|trans }}`;
		const _Swal_warning = `{{ "Avertissement"|trans }}`;
		const _Swal_Deletion = `{{ "Supprimé"|trans }}`;
		const _Swal_Update = `{{ "Mise à jour"|trans }}`;
		const _Swal_Add = `{{ "Ajouter"|trans }}`;
		const _Active = `{{ "Active"|trans }}`;
		const _Actif = `{{ "Actif"|trans }}`;
		const _Validated = `{{ "Validé"|trans }}`;
		const _Suspended = `{{ "Suspendu"|trans }}`;
		const _Information = `{{ "information"|trans }}`;
		const _Validation_request = `{{ "Vous êtes sûr de vouloir faire cette validation ?"|trans }}`;
		
		const _Rejected = `{{ "Rejeté"|trans }}`;
		const _Terminated = `{{ "Terminé"|trans }}`;
		const _Pending = `{{ "En attente"|trans }}`;
		const _Deleted = `{{ "Supprimé"|trans }}`;
		const _Disabled = `{{ "Désactivé"|trans }}`;
		const _Activated = `{{ "Activé"|trans }}`;
		const _Deletion_request = `{{ "Êtes-vous sûr de vouloir supprimer ?"|trans }}`;
		const _Deletion_yes = `{{ "Oui, supprimé!"|trans }}`;
		const _Deletion_no = `{{ "Non, annulé!"|trans }}`;
		const _Deletion_no_completed = `{{ "Suppression non effectuée"|trans }}`;

		///Laureates traduction on javascript
		const _Pseudo_Validation  = `{{ "Le pseudo est obligatoire"|trans }}`;
		const _Firstname_Validation            = `{{ "Le prénom est obligatoire"|trans }}`;
		const _Lastname_Validation             = `{{ "Le nom de famille est obligatoire"|trans }}`;
		const _Keyword_Validation             = `{{ "Le mot clé est obligatoire"|trans }}`;
		const _Phone_Validation             = `{{ "Le téléphone est obligatoire"|trans }}`;
		const _Email_Validation             = `{{ "L'e-mail est obligatoire"|trans|raw }}`;
		const _Event_Validation             = `{{ "L'événement est obligatoire"|trans|raw }}`;
		const _Category_Validation          = `{{ "Il faut au moins une catégorie"|trans }}`;
		
		// Forms Profile 
		const _Form_Error_Swal		=	`{{ "Désolé, il semble que des erreurs aient été détectées, veuillez réessayer."|trans}}`;
		const _Form_Ok_Swal			=	`{{ "Merci, vous avez mis à jour vos informations de base."|trans}}`;
		const _Form_Reset_Code      =   `{{ "Réinitialisation du mot de passe envoyé. Veuillez vérifier votre boite mail."|trans}}`;
		const _Form_Password_Required   =   `{{ "La confirmation du mot de passe est obligation."|trans}}`;
		const _Form_Error_Confirm   =   `{{ "Le mot de passe et sa confirmation ne sont pas les mêmes."|trans}}`;
		const _Form_Required_NewPassword    =   `{{ "Un nouveau mot de passe est nécessaire."|trans}}`;
		const _Form_Required_CurrentPassword    =   `{{ "Le mot de passe actuel est obligatoire."|trans}}`;
		const _Form_Invalid_Mail        =   `{{ "La valeur n'est pas une adresse électronique valide."|trans|raw}}`;
		const _Form_Required_Mail       =   `{{ "L'e-mail est obligatoire."|trans|raw}}`;
		const _Disable_Checker          =   `{{ "Veuillez cocher la case pour désactiver votre compte."|trans}}`;
		const _Disable_Confirm          =   `{{ "Êtes-vous sûr de vouloir désactiver votre compte ?"|trans}}`;
		const _disabled_question          =   `{{ "Êtes-vous sûr de vouloir désactiver ce compte ?"|trans}}`;
		const _enabled_question          =   `{{ "Êtes-vous sûr de vouloir activer ce compte ?"|trans}}`;
		const _Yes                      =   `{{ "Oui"|trans}}`;
		const _No                       =   `{{ "Non"|trans}}`;
		const _Ok                       =   `{{ "OK"|trans}}`;
		const _Confirm_Disable_Account  =   `{{ "Votre compte est désactivé."|trans}}`;
		const _Confirm_Active_Account  =   `{{ "Votre compte est activé avec succès."|trans}}`;
		const _Confirm_Active_Account_Already	=	`{{ "Votre compte est déjà activé."|trans}}`;
		const _confirm_disable  =   `{{ "Oui, désactivé."|trans}}`; 
		const _confirm_enable  =   `{{ "Oui, activé."|trans}}`; 
		const _Not_Disable_Account      =   `{{ "Le compte n'est pas désactivé !"|trans|raw}}`;
		
		// Payment methods
		const _Payment_Holder_Required  =   `{{ "Le nom du titulaire est obligatoire."|trans}}`;
		const _Payment_Bank_Required  =   `{{ "Le nom de la banque est obligatoire."|trans}}`;
		const _Payment_Country_Required  =   `{{ "Le pays est obligatoire."|trans}}`;
		const _Payment_Account_Required  =   `{{ "Le numéro de compte est obligatoire."|trans}}`;
		const _Visa_Number_Required  =   `{{ "Le numéro de la carte est obligatoire."|trans}}`;
		const _Visa_Number_Valid  =   `{{ "Le numéro de la carte n'est pas valide."|trans|raw}}`;
		const _Phone_Number_Required    =   `{{ "Un numéro de téléphone est obligatoire."|trans}}`;
		const _Phone_Not_Valid          =   `{{ "Le numéro de téléphone n'est pas valide."|trans|raw}}`;
		const _Operator_Required        =   `{{ "Un opérateur mobile est obligatoire."|trans}}`;
		const _Request_fail             =   `{{ "Échec de la requête!"|trans}}`;
		const _Cancel_Question          =   `{{ "Êtes-vous sûr de vouloir annuler ?"|trans}}`;
		const _yes_cancel               =   `{{ "Oui, annulé!"|trans}}`;
		const _not_cancel               =   `{{ "Echèc de l'annulation."|trans|raw}}`;
		const _Return                   =   `{{ "Non, retour!"|trans}}`;
		const _Bank_Account_Number_Invalid  =   `{{ "Le numéro de compte ne doit contenir que des chiffres."|trans}}`;
		const _Payment_SWIFT_Required =   `{{ "SWIFT est obligatoire"|trans}}`;
		const _Payment_SWIFT_Length =   `{{ "SWIFT doit contenir entre 8 et 11 caractères."|trans}}`;
		const _Payment_RIB_Required =   `{{ "Le document RIB est obligatoire."|trans}}`;
		const _Payment_ID_Required =   `{{ "La pièce d'identité est obligatoire."|trans|raw}}`;
		const _no_cancel         = `{{ "Non, annulé!"|trans }}`;
		const _isRequired       =   `{{ "est obligatoire"|trans }}`;
		const _CVV2_only_digit  =   `{{ "CVV2 ne doit contenir que des chiffres."|trans}}`;
		const _CVV2_Lenght      =   `{{ "Le CVV2 ne doit contenir que 3 ou 4 chiffres."|trans}}`;
		const _File_Required    =   `{{ "Le fichier est obligatoire"|trans}}`;
		const _redirect_fedapay =	`{{ "Vérification du numéro de téléphone. Un paiement de 100 XOF par Mobile Money sera initialisé."|trans}}`;
		const _no_success_fedapay =	`{{ "La transaction de vérification a échoué, veuillez reprendre la validation de votre numéro."|trans}}`;

		// Profile Affiliate
		const _Profile_Affiliate_Overview   =   `{{ "Vue d'ensemble de l'affilié"|trans|raw}}`;
		const _Profile_Affiliate_Add   =   `{{ "Ajouter un affilié"|trans}}`;
		const _Profile_Affiliate_Edit   =   `{{ "Modifier un affilié"|trans}}`;

		// Form validation
		const _FirstName_Required   =   `{{ "Le prénom est obligatoire."|trans}}`;
		const _LastName_Required   =   `{{ "Le nom de famille est obligatoire."|trans}}`;
		const _Email_Required   =   `{{ "L'e-mail est obligatoire."|trans|raw}}`;
		const _Not_Valid_Mail   =   `{{ "La valeur n'est pas une adresse e-mail valide."|trans|raw}}`;
		const _Password_Required   =   `{{ "Le mot de passe est obligatoire."|trans}}`;
		const _Password_Lenght   =   `{{ "Le mot de passe doit comporter au moins 8 caractères."|trans}}`;
		const _Password_equal   =   `{{ "Le mot de passe ne peut pas être le même que le nom d'utilisateur."|trans|raw}}`;
		const _Phone_Required   =   `{{ "Le numéro de téléphone est obligatoire."|trans}}`;
		const _Country_Required   =   `{{ "Veuillez sélectionner un pays."|trans}}`;
		const _confirm_redirect	=	`{{ "Recharger la page."|trans}}`;
		const _ajax_datatable_error	=	`{{ "Votre session a expirée."|trans}}`;
		const _Password_Valid	=	`{{ "Veuillez saisir un mot de passe valide"|trans}}`;
		const _Password_Confirm	=	`{{ "La confirmation du mot de passe est requise"|trans}}`;
		const _Condition_Confirm	=	`{{ "Vous devez accepter les Termes et Conditions"|trans}}`;
		const _Account_Error	=	`{{ "Une erreur inattendue s'est produite lors de la création de votre compte. Veuillez réessayer"|trans|raw}}`;
		const _Role_Required	=	`{{ "Le choix d'un rôle est requis"|trans|raw}}`;
		const _Promoter_Required	=	`{{ "Le choix du promoteur de l'affilier est requis"|trans|raw}}`;
		const _Confirmation_Required	=	`{{ "Veuillez cocher la confirmation"|trans}}`;
		const _RoleName_Required	=	`{{ "Le nom du rôle est requis"|trans}}`;
		const _RoleCode_Required	=	`{{ "Le code du rôle est requis"|trans}}`;
		const _Form_NotCancel	=	`{{ "Votre formulaire n'a pas été annulé!."|trans|raw}}`;
		const _NotEmpty	=	`{{ "Ce champ ne peut pas être vide."|trans}}`;
		const _Percentage_Valid	=	`{{ "La valeur doit être supérieur à 0."|trans}}`;
 
		const _intl_invalid_number	=	`{{ "Numéro invalide"|trans}}`;
		const _intl_invalid_country_code	=	`{{ "Code pays invalide"|trans}}`;
		const _intl_short	=	`{{ "Trop court"|trans}}`;
		const _intl_long	=	`{{ "Trop long"|trans}}`;
 