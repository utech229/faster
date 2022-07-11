<?php

namespace App\Controller;

use App\Service\uBrand;
use App\Entity\Brand;
use App\Entity\Sender;

use App\Service\BaseUrl;
use App\Service\Services;
use App\Entity\User;
use App\Entity\Recharge;
use App\Entity\Status;
use App\Service\AddEntity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\UserRepository;
use App\Repository\SenderRepository;
use App\Repository\BrandRepository;
use Symfony\Component\HttpFoundation\File\UploadedFile;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Dompdf\Dompdf;
use Dompdf\Options;

#[IsGranted("IS_AUTHENTICATED_FULLY")]
#[IsGranted("ROLE_USER")]
class BrandController extends AbstractController
{
    public function __construct(BaseUrl $baseUrl, Services $services, uBrand $brand, TranslatorInterface $translator, EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator, UserRepository $userRepository, SenderRepository $senderRepository, BrandRepository $brandRepository, AddEntity $addEntity){
        $this->baseUrl         = $baseUrl;
        $this->urlGenerator    = $urlGenerator;
        $this->intl            = $translator;
        $this->services        = $services;
        $this->brand           = $brand;
        $this->em	           = $entityManager;
        $this->addEntity	   = $addEntity;
        $this->uRepository     = $userRepository;
        $this->bRepository     = $brandRepository;
        $this->sRepository     = $senderRepository;
        $this->permission      = ["BRND0", "BRND1", "BRND2", "BRND3", "BRND4", "BRND5"];
        $this->pAccess         = $this->services->checkPermission($this->permission[0]);
        $this->pCreate         = $this->services->checkPermission($this->permission[1]);
        $this->pUpdate         = $this->services->checkPermission($this->permission[3]);
        $this->pADelete        = $this->services->checkPermission($this->permission[4]);
        $this->pAllAccess      = $this->services->checkPermission($this->permission[2]);
        $this->ext             = ['jpg', 'png' , 'JPG', 'PNG', 'JPEG'];

    }

    //Load index page of brand
    #[Route('{_locale}/brand', name: 'brand')]
    public function index(): Response
    {
        if(!$this->pAccess){
            $this->addFlash('error', $this->intl->trans("Tentative d'accès à la page marque échouée.")); return $this->redirectToRoute("app_home");
        }
        $inBrand   = $users = false;
        $checkEtat = $this->services->checkThisUser($this->pAllAccess);
        switch($checkEtat[0]){
            case 0: $inBrand  = true; $users = $this->uRepository->findAll(); break;
            case 1: $inBrand  = true; $users = $this->services->getUserByPermission('MANGR'); break;
            case 2: $inBrand  = $this->bRepository->findOneBy(['manager'=> $this->getUser()]); break;
            case 4: $inBrand  = $this->bRepository->findOneBy(['manager'=> $this->getUser()]); break;
            default: $inBrand = $this->bRepository->findOneBy(['manager'=> $this->getUser()->getAffiliationManager()]); break;
        }

        return $this->render('brand/index.html.twig', [
            'controller_name' => 'BrandController',
            'brand'           => $this->brand->get(),
            'sStatus'         => $this->em->getRepository(Status::class)->findByCode([6,2,7]),
            'pAccess'         => $this->pAccess,
            'pAllAcess'       => $this->pAllAccess,
            'sBrand'          => $this->bRepository->findByStatus($this->services->status(3)),
            'baseUrl'         => $this->baseUrl->init(),
            'title'           => $this->intl->trans('Mes marques'),
            'pageTitle'       => [
                                    [$this->intl->trans('Gestion des marques')],
            ],
            'inBrand'         => $inBrand,
            'users'           => $users
        ]);
    }

    #[Route('{_locale}/create_brand', name: 'create_brand')]
    public function createBrand(Request $request): Response
    {
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour utiliateur échouée"));
        if(!$this->pAccess){return $this->services->msg_error(
            $this->intl->trans("Tentative de validation de marque blanche échouée."), $this->intl->trans("Vous n'êtes pas autorisé(e) à effectuer cette action.")
        );}
        $linkLogo   = 'loadPicture';

        // $buildImg = $this->addEntity->profilePhotoSetter($request , $this->getUser());
        // $buildImg = $this->addEntity->imageSetter($request , $this->getUser());

        $user       = $this->getUser();
        $isSelect   = $request->request->get('uSelect');
        if($isSelect){
            $user = $this->uRepository->findOneBy(['uid'=> $isSelect, 'status'=> $this->services->status(3)]);
            if(!$user) return $this->services->msg_error($this->intl->trans("Utilisateur incorrect.").': '.$this->getUser()->getEmail(), $this->intl->trans("L'utilisateur sélectionné n'existe pas ou n'est pas actif."));
        }

        $executeReq  = $this->services->checkThisUser($this->pAllAccess, NULL, NULL, $user);
        if($executeReq[0]== 0 || $executeReq[0] == 1){
            $manager = $user;
        }else{
            $manager = $this->getUser();
        }
        $creator = $this->getUser();
        if($request->request->get('thisU')){
            $inBrand = $this->bRepository->findOneBy(['uid'=> $request->request->get('thisU')]);
            //Permettre également à l'utilisateur de pouvoir changer le statut
            if($inBrand){
                $sender = $this->checkSender($executeReq[0], $request->request->get('_sender'), $manager, 1);
                if(!$sender[0]) return $this->services->msg_error($this->intl->trans("Expéditeur existant."), $this->intl->trans("Cet expéditeur existe déjà. Veuillez créer un nouvel expéditeur pour continuer."));

                /** @var UploadedFile $logo */
                $logo = $request->files->get("_logo");
                if (isset($logo) && $logo->getError() == 0) {$loadFile  = $this->services->checkFile($logo, $this->ext, 1024000);
                    if($loadFile['error'] ==false ){ $targetPath = "app/uploads/brands/logos/"; $new_file_name  = $inBrand->getUid();
                        $upload_result  = $this->services->renameFile($logo, $targetPath, 1, $new_file_name, $new_file_name);
                    }else{
                        return $this->services->msg_error($this->intl->trans("Image logo sélectionnée incorrecte."), $this->intl->trans("Le format du logo que vous avez sélectionné est incorrect ou la taille est trop grande. veuillez choisir une autre image SVP."));
                    }
                    // dd($upload_result);
                }
                $inBrand-> setManager($manager)
                        -> setName($request->request->get('_name_brand'))
                        -> setSiteUrl($request->request->get('_url_brand'))
                        -> setLogo('$buildImg')
                        -> setFavicon('$buildImg')
                        -> setEmail($request->request->get('_mail_support'))
                        -> setNoreplyEmail(("noreply".strstr($request->request->get('_mail_support'), '@')))
                        -> setIsDefault(1)
                        -> setPhone($request->request->get('_phone_support'))
                        -> setUpdatedAt(new \DatetimeImmutable())
                        -> setDefaultSender(($sender[1]) ? $sender[1] : $this->createSender($manager, $request->request->get('_sender')))
                        -> setCreator($creator);
                        // -> setObservations($request->request->get('observations'));
                        $this->bRepository->add($inBrand);
                $msg1 =$this->intl->trans("Modification de marque blanche").': '.$this->getUser()->getEmail();
                $msg2 = $this->intl->trans("La marque a été modifiée avec succès.");
            }else{
                return $this->services->msg_error($this->intl->trans("La marque est inexistante."), $this->intl->trans("Impossible de continuer cette action ! Contactez les administrateurs si vous pensez que c'est une erreur."));
            }
        }else{
            $existBrand = $this->bRepository->findOneBy(['name'=> $request->request->get('_name_brand')]);
            if($existBrand){ return $this->services->msg_error($this->intl->trans("Création de marque échouée."), $this->intl->trans("Ce nom de marque n'est pas disponible. Veuillez changer le nom pour continuer."));}
            $sender = $this->checkSender($executeReq[0], $request->request->get('_sender'), $manager);
            if(!$sender[0]) return $this->services->msg_error($this->intl->trans("Expéditeur existant."), $this->intl->trans("Cet expéditeur existe déjà. Veuillez créer un nouvel expéditeur pour continuer."));

            $uid = $this->services->getUniqid();
            /** @var UploadedFile $logo */
            $logo = $request->files->get("_logo");
            if (isset($logo) && $logo->getError() == 0) {$loadFile  = $this->services->checkFile($logo, $this->ext, 1024000);
                if($loadFile['error'] ==false ){$targetPath     = "app/uploads/brands/logos/"; $new_file_name  = $uid;
                    $upload_result  = $this->services->renameFile($logo, $targetPath, 1, $new_file_name, $new_file_name);
                }else{
                    return $this->services->msg_error($this->intl->trans("Image logo sélectionnée incorrecte."), $this->intl->trans("Le format du logo que vous avez sélectionné est incorrect ou la taille est trop grande. veuillez choisir une autre image SVP."));
                }
            }

            $newBrand   = new Brand;
            $newBrand   -> setManager($manager)
                        -> setStatus($this->services->status(1))
                        -> setUid($uid)
                        -> setName($request->request->get('_name_brand'))
                        -> setSiteUrl($request->request->get('_url_brand'))
                        -> setLogo($upload_result)
                        -> setFavicon($upload_result)
                        -> setEmail($request->request->get('_mail_support'))
                        -> setNoreplyEmail(("noreply".strstr($request->request->get('_mail_support'), '@')))
                        -> setIsDefault(1)
                        -> setPhone($request->request->get('_phone_support'))
                        -> setCreatedAt(new \DatetimeImmutable())
                        -> setCommission(0)
                        -> setDefaultSender($this->createSender($manager, $request->request->get('_sender')))
                        -> setCreator($creator);
            $this->bRepository->add($newBrand);
            $msg1 =$this->intl->trans("Création de marque ").': '.$this->getUser()->getEmail();
            $msg2 = $this->intl->trans("Votre dossier de création de marque a été soumis avec succès.");
            //Envoie de mail pour la création de marque blanche
        }
        return $this->services->msg_success($msg1,$msg2);
    }

    //Make sender
    public function createSender($manager, $_sender){
        $newSender  = new Sender;
        $newSender  -> setManager($manager)
                    -> setStatus($this->services->status(1))
                    -> setUid($this->services->getUniqid())
                    -> setName($_sender)
                    -> setCreatedAt(new \DatetimeImmutable())
                    -> setCreateBy($this->getUser());
        $this->sRepository->add($newSender);
        return $newSender;
    }
    //Use this function notify sender in dataBase
    public function checkSender($typeUser, $sender, $manager, $update = 0){
        //si 1 continuer...
        $eSender    = false;
        $inSender   = $this->sRepository->findOneBy(['name'=> $sender]);

        if($inSender) {
            if($update){
                if($typeUser == 0 || $typeUser == 1){
                    //dd("Admin");
                    if($manager->getId() == $inSender->getManager()->getId()) $eSender = true;
                }else{
                    if($typeUser == 3 || $typeUser == 2 ){
                        if($this->getUser()->getAffiliateManager()->getId() == $inSender->getManager()->getId()){$eSender = true;};
                        // dd("Revendeur", "utilisateur");
                    }else{
                        if($this->getUser()->getId() == $inSender->getManager()->getId()){ $eSender = true;}
                        // dd("affilié revendeur", "affilié user");
                    }
                }
            }
        }else{
            $eSender = true;
        }
        // $test = ($inSender) ? $inSender:'';
        // // dd($eSender, $test);
        return [$eSender, ($inSender) ? $inSender:''];
    }
    //This function reload brand
    #[Route('{_locale}/rbrand', name: 'rbrand')]
    public function validateBrand(Request $request): Response
    {
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur "));
        $executeReq = $this->services->checkThisUser($this->pAllAccess);
        if($executeReq[0]== 0 || $executeReq[0] == 1){
            $inBrand = $this->bRepository->findOneBy(['uid'=> $request->request->get('key')]);
            if($inBrand){
                $inBrand-> setStatus($this->services->status($request->request->get('st')))
                        -> setUpdatedAt(new \DatetimeImmutable())
                        -> setObservations($request->request->get('observations'))
                        -> setValidator($this->getUser());
                $this->bRepository->add($inBrand);
                return $this->services->msg_success($this->intl->trans("Changement de statut."), $this->intl->trans("Le statut de la marque a été changé avec succès."));
            }
        }else{
            return $this->services->msg_error(
                $this->intl->trans("Tentative de validation de marque blanche échouée."),
                $this->intl->trans("Vous n'êtes pas autorisé(e) à effectuer cette action.")
            );
        }
    }

    //Use this function for check all brand make
    public function getBrand($allAcess = NULL, $user = NULL, $manager = NULL){
        if($allAcess){
            $allBrand = $this->bRepository->findAll();
        }else if($manager){
            $allBrand = [];
            $getUsers  = $this->getUserByPermission('MANGR');
            foreach($getUsers as $getUser){
                $data = [];
                if($this->bRepository->findBy(['manager'=> $getUser->getUser])){$data[] = $this->bRepository->findBy(['manager'=> $getUser->getUser]); $allBrand[] = $data;}
            }
        }else{
            $allBrand = $this->bRepository->findBy(['manager'=> $user]);
        }
        return $allBrand;
    }

    //This function is used to check brand info
    #[Route('{_locale}/checkbrand', name: 'check_brand')]
    public function checkbrand(Request $request){
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        $checkBrand      = $this->bRepository->findOneBy(['uid'=> $request->request->get('b') ]);
        if(!$checkBrand){return $this->services->msg_error(
            $this->intl->trans("Récupération détails de marque échouée."),
            $this->intl->trans("La marque n'existe plus ou une erreur s'est produite. Si vous pensez à une erreur, contactez les administrateurs.")
        );}
        $infoBrand = [
                        'name'      => $checkBrand->getName(),
                        'manager'   => [$checkBrand->getManager()->getUid(), $checkBrand->getUid()],
                        'urlSite'   => $checkBrand->getSiteUrl(),
                        'adressS'   => $checkBrand->getEmail(),
                        'sender'    => $checkBrand->getDefaultSender()->getName(),
                        'phone'     => $checkBrand->getPhone(),
                        'uriLogo'   => $checkBrand->getLogo(),
                        'status'    => $checkBrand->getStatus()->getCode(),
                        // 'observation'    => $checkBrand->getStatus()->getCode()
        ];
        $this->services->addLog($this->intl->trans("Récupération des informations d'une marque de revente de la solution."));
        return new JsonResponse($infoBrand);
    }
    //This function is used to check all brand
    #[Route('{_locale}/loadbrand', name: 'load_brand')]
    public function loadbrand(Request $request){
        if(!$this->isCsrfTokenValid($this->getUser()->getUid(), $request->request->get('_token'))) return $this->services->no_access($this->intl->trans("Initialisation d'une recharge pour l'utiliateur ").': '.$this->getUser()->getEmail());
        if(!$this->pAccess){$this->addFlash('error', $this->intl->trans("Vous n'êtes pas autorisé(e) pour accéder à cette page !")); return $this->redirectToRoute("app_home");}
        $checkEtat = $this->services->checkThisUser($this->pAllAccess);
        switch($checkEtat[0]){
            case 0: $allBrand  = $this->getBrand($this->pAllAccess); break;
            case 1: $allBrand  = $this->getBrand($this->pAllAccess, NULL, 1); break;
            case 2: $allBrand  = $this->getBrand($this->pAllAccess, $this->getUser()); break;
            case 3: $allBrand  = $this->getBrand($this->pAllAccess, $this->getUser()->getAffiliateManager()); break;
            case 4: $allBrand  = $this->getBrand($this->pAllAccess, $this->getUser()); break;
            default: $allBrand = $this->getBrand($this->pAllAccess, $this->getUser()->getAffiliateManager()); break;
        }
        $data = [];
        if($allBrand){
            foreach($allBrand as $getBrand){
                $row                   = array();
                $row['brand']          = $getBrand->getName();
                $row['administrator']  = $getBrand->getManager()->getEmail();
                $row['urlSite']        = $getBrand->getSiteUrl();
                $row['emailV']         = ($getBrand->getValidator()) ? $getBrand->getValidator()->getEmail() : '';
                $row['sender']         = $getBrand->getDefaultSender()->getName();
                $row['status']         = $getBrand->getStatus()->getCode();
                $row['createdAt']      = $getBrand->getCreatedAt()->format("d-m-Y H:i");
                $row['action']         = [
                                            'uid'       => $getBrand->getUid(),
                                            'status'    => $getBrand->getStatus()->getCode(),
                                            'pvalidate' => $this->pAllAccess,
                                            'link'      => 'https://'.$getBrand->getSiteUrl(),
                                            'name'      => $getBrand->getName(),
                                            'observations'=> $getBrand->getObservations()
                ];
                $data[]                = $row;
            }
        }
        $output = array("data" => $data);
        $this->services->addLog($this->intl->trans('Récupération de la liste des marques créées.'));
        return new JsonResponse($output);
    }

    //This function is used to download pe
    #[Route('{_locale}/download', name: 'download')]
    public function download(Request $request):Response{
        $options = new Options();
        $options ->get('defaultFont', 'Roboto');
        $dompdf  = new Dompdf($options);

        $img     = $this->serializeImg($path);
        $urban   = "https://www.fastermessage.com/app/public/assets/admin/images/logo/urban.png";
        $data    = [
                    'brand'       => $brand,
                    'adress'      => $adress,
                    'gerant'      => $gerant,
                    'montant'     => $EventExist->getMontant(),
                    'ImgLink'     => $img,
                    'devise'      =>$factureData->getDevise(),
                    'ImgAdmin'    => $this->serializeImg($urban),
                    'message'     => $translator->trans('Recharge de compte sur ').$brand,
                    'name'        => $name,
                    'phoneAdmin'  => $phone,
                    'RCCM'        => $rccm,
                    'IFU'         => $ifu,
                    'reference'   => $EventExist->getReference(),
                    'phoneClient' => $factureData->getPhone(),
                    'AdressClient'=> $factureData->getAdress(),
                    'emailSender' => $email,
                    'phoneTeam'   => $phoneTeam,
                    'UrlSite'     => $siteWeb,
                    'datepay'     => $EventExist->getHoroDate(),
                    'ImgPaid'     => $this->serializeImg("https://www.fastermessage.com/app/public/assets/admin/images/logo/paid.jpg")
        ];

        $html           = $this->renderView('pdf/pdf.html.twig', [
            'headline'  => $brand.$translator->trans(" - FACTURE"), 'compte'=>$user, 'data'=>$data
        ]);
        $dompdf         ->loadHtml($html, 'UTF-8');
        $dompdf         ->setPaper('A4', 'portrait');
        $dompdf         ->render();
        $fichier        = $dompdf->stream($brand.$translator->trans("-FACTURE-CLIENT-").$factureData->getUid(),
                        [
                            "Attachment"=> true
                        ]);
        return $this->json(array('message' => $fichier, 'status' =>'success', 200));
    }

}
