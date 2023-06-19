<?php

    namespace App\Controllers;

    use App\Dao\AccountDao;
    use App\Dao\AvatarDao;
    use App\Models\AvatarModel;
    use App\Services\AccountService;
    use App\Services\AvatarService;
    use App\Services\FileUploadService;
    use App\Services\ProvisioningService;
    use App\Services\StorageService;
    use App\Utils\Thumbnail;
    use DateTime;
    use Exception;

    class AvatarController
    {
        private $container;

        public function __construct($container) {
            $this->container = $container;
        }

        public function getDefaultContestImage($request, $response, $args) {
            
            $initials = strtoupper($args['initial']);

            $virtual_image = imagecreate(200,200);
            // $red = rand(0,255);
            // $green = rand(0,255);
            // $blue = rand(0,255);
            $red = 240;
            $green = 240;
            $blue = 240;
            imagecolorallocate($virtual_image,$red,$green,$blue);
            // $textcolor = imagecolorallocate($virtual_image,255,255,255);
            // $text_red = 245;
            // $text_green = 128;
            // $text_blue = 76;
            $text_red = 140;
            $text_green = 140;
            $text_blue = 140;
            $textcolor = imagecolorallocate($virtual_image,$text_red,$text_green,$text_blue);

            $font = WWWROOT . '/public/css/fonts/OpenSans-Bold.ttf';

            // Get image dimensions
            $width = imagesx($virtual_image);
            $height = imagesy($virtual_image);

            // Get center coordinates of image
            $centerX = $width / 2;
            $centerY = $height / 2;

            // configure fontsize and angle
            $font_size = 75;
            $angle = 0;

            // Get size of text
            list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font, $initials);

            $left_offset = ($right - $left) / 2;
            $top_offset = ($bottom - $top) / 2;

            // Generate coordinates
            $x = $centerX - $left_offset;
            $y = $centerY + $top_offset;

            imagettftext($virtual_image, $font_size, $angle, $x, $y, $textcolor, $font, $initials);
            
            imagejpeg($virtual_image);
            @imagedestroy($virtual_image);

            return $response = $response->withHeader('Content-type', 'image/jpg')->withHeader('Cache-Control','max-age=86400'); // use cache

        }

        public function getDefaultThumbnail($request, $response, $args) {
            
            // obtem a referencia da conta do usuario
            // $account = $this->container->auth->getUserAccount();

            $initials = strtoupper($args['initials']);

            $virtual_image = imagecreate(200,200);
            // $red = rand(0,255);
            // $green = rand(0,255);
            // $blue = rand(0,255);
            
            // Gray
            $red = 240;
            $green = 240;
            $blue = 240;
            
            imagecolorallocate($virtual_image,$red,$green,$blue);
            // $textcolor = imagecolorallocate($virtual_image,255,255,255);
            
            // Gray
            // $text_red = 140;
            // $text_green = 140;
            // $text_blue = 140;

            // Orange
            $text_red = 245;
            $text_green = 144;
            $text_blue = 61;
            
            $textcolor = imagecolorallocate($virtual_image,$text_red,$text_green,$text_blue);

            $font = WWWROOT . '/public/css/fonts/OpenSans-Bold.ttf';

            // Get image dimensions
            $width = imagesx($virtual_image);
            $height = imagesy($virtual_image);

            // Get center coordinates of image
            $centerX = $width / 2;
            $centerY = $height / 2;

            // configure fontsize and angle
            $font_size = 50;
            $angle = 0;

            // Get size of text
            list($left, $bottom, $right, , , $top) = imageftbbox($font_size, $angle, $font, $initials);

            $left_offset = ($right - $left) / 2;
            $top_offset = ($bottom - $top) / 2;

            // Generate coordinates
            $x = $centerX - $left_offset;
            $y = $centerY + $top_offset;

            imagettftext($virtual_image, $font_size, $angle, $x, $y, $textcolor, $font, $initials);
            
            imagejpeg($virtual_image);
            @imagedestroy($virtual_image);

            return $response = $response->withHeader('Content-type', 'image/jpg')->withHeader('Cache-Control','max-age=86400'); // use cache
        }

        // public function getAccountAvatar($request, $response, $args) {            
        //     // // obtem a referencia da conta do usuario
        //     // $account = $this->container->auth->getUserAccount();

        //     // // // obtem o e-mail da URL
        //     // $email = $args['email'];

        //     // $account = AccountService::retrieveBy('email',$email);
        //     // $avatar = AvatarService::retrieveBy('id_account',$account->id);

        //     // $src = $avatar->src_file;
        //     // if( !file_exists($src) )
        //     //     return false;
            
        //     // // show avatar image
        //     // $avatar->getImage();

        //     $email = $args['email'];
        //     $src = AVATARS_PATH . DIRECTORY_SEPARATOR . $email . ".jpg";

        //     // // configura o thumbnail
        //     $desired_width = 100;

        //     /* read the source image */
        //     $source_image = imagecreatefromjpeg($src);
        //     $width = imagesx($source_image);
        //     $height = imagesy($source_image);
        
        //     /* find the "desired height" of this thumbnail, relative to the desired width  */
        //     $desired_height = floor($height * ($desired_width / $width));
        
        //     /* create a new, "virtual" image */
        //     $virtual_image = imagecreatetruecolor($desired_width, $desired_height);
        
        //     /* copy source image at a resized size */
        //     imagecopyresampled($virtual_image, $source_image, 0, 0, 0, 0, $desired_width, $desired_height, $width, $height);
                    
        //     imagejpeg($virtual_image);
        //     @imagedestroy($virtual_image);

        //     return $response = $response->withHeader('Content-type', 'image/jpg'); // do not use cache
        //     // return $response = $response->withHeader('Content-type', $avatar->getMimeType())->withHeader('Cache-Control','public, max-age=3600'); // use cache

        // }

        // public function uploadAvatar($request, $response, $args) {

        //     //
        //     // obtem os arquivos enviados via formulario
        //     //
        //     $uploadedFiles = $request->getUploadedFiles();
        //     $uploadedFile = $uploadedFiles['image'] ?? null;

        //     if($uploadedFile->getError() !== UPLOAD_ERR_OK) {
        //         $this->container->flash->addMessage('snackbar','Nenhmum arquivo enviado.');
        //         return $response->withRedirect($this->container->router->pathFor('profile.avatar'));
        //     }

        //     //
        //     // validar o tipo do arquivo
        //     //
        //     $mediaTypes = [
        //         'image/jpg',
        //         'image/jpeg',
        //         'image/png'
        //     ];
        //     if( !in_array($uploadedFile->getClientMediaType(),$mediaTypes) ) {
        //         $this->container->flash->addMessage('snackbar','Formato de arquivo inválido.');
        //         return $response->withRedirect($this->container->router->pathFor('profile.avatar'));
        //     }

        //     //
        //     // validar o tamanho do arquivo
        //     //
        //     $size = ROUND($uploadedFile->getSize()/1024); 
        //     if( $size > 1024 ) {
        //         $this->container->flash->addMessage('snackbar','Tamanho limite excedido.');
        //         return $response->withRedirect($this->container->router->pathFor('profile.avatar'));
        //     }

        //     //
        //     // Gravar o arquivo
        //     //
        //     try {

        //         $fileUploadService = new FileUploadService($uploadedFile);
        //         $file = $fileUploadService->moveUploadedFileTo( UPLOADS_PATH );

        //         $email = $this->container->auth->getUserAccount()->getEmail();
        //         $dest = AVATARS_PATH . DIRECTORY_SEPARATOR . $email . ".jpg";
        //         $thumb = new Thumbnail($file);
        //         $thumb->createThumb($dest);

        //         FileUploadService::deleteFile($file);
                
        //         $this->container->flash->addMessage('snackbar','Arquivo atualizado.');
                
        //     } catch (Exception $e) {
        //         $this->container->flash->addMessage('snackbar',$e->getMessage());
        //     }
            
        //     return $response->withRedirect($this->container->router->pathFor('profile.avatar'));
                        
            
        //     // //$uploadsDirectory = UPLOADS_PATH;
        //     // //$filename = $fileUploadService->moveUploadedFileTo($uploadsDirectory);
        //     // $filename = $fileUploadService->moveUploadedFileTo( FileUploadService::UPLOADS_PATH );
                    
        //     // // get account reference
        //     // $account = $this->container->auth->getUserAccount();
        //     // $storageService = new StorageService($account);

        //     // $uploadedSrcFile = FileUploadService::UPLOADS_PATH . DIRECTORY_SEPARATOR . $filename;
        //     // $moveTo = $storageService->getImageStoragePath();
        //     // $srcFile = $storageService->moveFile( $uploadedSrcFile, $moveTo );
            
        //     // $avatar = AvatarService::retrieveBy('id_account',$account->id);
        //     // if (!$avatar) {
        //     //     $avatar = new AvatarModel;
        //     //     $avatar->id_account = $account->id;
        //     //     $avatar = AvatarService::create($avatar);
        //     // }

        //     // // salvar para remover no futuro
        //     // $oldAvatar = $avatar->src_file;

        //     // $avatar->file = $filename;
        //     // $avatar->src_file = $srcFile;
        //     // AvatarService::update($avatar);

        //     // $avatar->cropImage(); // crop image
        //     // // $avatar->cropJPEGImage(); // crop image

        //     // // remover o arquivo antigo
        //     // StorageService::deleteFile($oldAvatar);

        //     // $this->container->flash->addMessage('snackbar','Arquivo enviado com sucesso');
        //     // return $response->withRedirect($this->container->router->pathFor('profile'));
            
        // }

        // public function deleteAvatar($request, $response, $args) {
        //     // obtem a referencia da conta do usuario
        //     $email = $this->container->auth->getUserAccount()->getEmail();
        //     $src_file = AVATARS_PATH . DIRECTORY_SEPARATOR . $email . ".jpg";

        //     FileUploadService::deleteFile($src_file);

        //     // $avatar = AvatarService::retrieveBy('id_account',$account->id);
        //     // if (!$avatar)
        //     //     return $response->withRedirect($this->container->router->pathFor('profile'));    
            
        //     // StorageService::deleteFile($avatar->src_file);
        //     // AvatarService::delete($avatar->id);

        //     $this->container->flash->addMessage('snackbar','Arquivo removido com sucesso');
        //     return $response->withRedirect($this->container->router->pathFor('profile'));
        // }

        

        // public function postAdminAvatarForm($request, $response, $args) {
            
        //     // obtem a conta alvo
        //     $id = $args['id'];   
        //     $account = AccountService::retrieve($id);
        //     if(!$account) {
        //         // flash a message
        //         $this->container->flash->addMessage('snackbar','Falha ao obter a conta alvo');
        //         return $response->withRedirect($this->container->router->pathFor('dashboard'));
        //     }

        //     $params = $request->getParams();

        //     switch($params['action']) {
        //         // UPLOAD Avatar
        //         case 'upload':
        //             // obtem os arquivos enviados via formulario
        //             $uploadedFiles = $request->getUploadedFiles();
        //             $uploadedFile = $uploadedFiles['image'] ?? null;
        //             if($uploadedFile->getError() !== UPLOAD_ERR_OK) {
        //                 $this->container->flash->addMessage('snackbar','Nenhmum arquivo enviado.');
        //                 return $response->withRedirect($this->container->router->pathFor('dashboard.account.avatar',['id' => $account->getId()]));
        //             }

        //             // validar o tipo do arquivo
        //             $mediaTypes = [
        //                 'image/jpg',
        //                 'image/jpeg',
        //                 'image/png'
        //             ];
        //             if( !in_array($uploadedFile->getClientMediaType(),$mediaTypes) ) {
        //                 $this->container->flash->addMessage('snackbar','Formato de arquivo inválido.');
        //                 return $response->withRedirect($this->container->router->pathFor('dashboard.account.avatar',['id' => $account->getId()]));
        //             }

        //             // validar o tamanho do arquivo
        //             $size = ROUND($uploadedFile->getSize()/1024); 
        //             if( $size > 1024 ) {
        //                 $this->container->flash->addMessage('snackbar','Tamanho limite excedido.');
        //                 return $response->withRedirect($this->container->router->pathFor('dashboard.account.avatar',['id' => $account->getId()]));
        //             }

        //             // Gravar o arquivo
        //             try {
        //                 $fileUploadService = new FileUploadService($uploadedFile);
        //                 $file = $fileUploadService->moveUploadedFileTo( UPLOADS_PATH );

        //                 $email = $account->getEmail();
        //                 $dest = AVATARS_PATH . DIRECTORY_SEPARATOR . $email . ".jpg";
        //                 $thumb = new Thumbnail($file);
        //                 $thumb->createThumb($dest);

        //                 FileUploadService::deleteFile($file);
                        
        //                 $this->container->flash->addMessage('snackbar','Arquivo atualizado.');
                        
        //             } catch (Exception $e) {
        //                 $this->container->flash->addMessage('snackbar',$e->getMessage());
        //             }
        //             break;
        //         // REMOVE Avatar
        //         case 'remove':
        //             $email = $account->getEmail();
        //             $src_file = AVATARS_PATH . DIRECTORY_SEPARATOR . $email . ".jpg";

        //             FileUploadService::deleteFile($src_file);

        //             $this->container->flash->addMessage('snackbar','Arquivo removido com sucesso');
        //             break;
        //         default:
        //     }
            
        //     return $response->withRedirect($this->container->router->pathFor('dashboard.account.avatar',['id' => $account->getId()]));
            
        // }

        // public function adminAvatarUpload($request, $response, $args) {
        //     // obtem a conta alvo
        //     $id = $args['id'];   
        //     $account = AccountService::retrieve($id);
        //     if(!$account) {
        //         // flash a message
        //         $this->container->flash->addMessage('snackbar','Falha ao obter a conta alvo');
        //         return $response->withRedirect($this->container->router->pathFor('dashboard'));
        //     }

        //     // obtem os arquivos enviados via formulario
        //     $uploadedFiles = $request->getUploadedFiles();
        //     $uploadedFile = $uploadedFiles['image'] ?? null;
        //     if(!$uploadedFile || $uploadedFile->getError() !== UPLOAD_ERR_OK) {
        //         return $response->withRedirect($this->container->router->pathFor('dashboard.account.edit',['id'=>$id]));
        //     }
            
        //     // realizar o upload de arquivo
        //     $fileUploadService = new FileUploadService($uploadedFile);
        //     try {
        //         if (!$fileUploadService->validateFileMaxSize(1024)) {
        //             throw new Exception('Tamanho limite de 1MB excedido');
        //         }
        //         if (!$fileUploadService->validateFileExtension(['image/jpeg','image/jpg','image/png'])) {
        //             // TODO
        //             // - aceitar mais de um formato de arquivo. Precisa gerar imagens internas para PNG alem de JPEG
        //             throw new Exception('Formato de arquivo inválido');
        //         }
        //     } catch (Exception $e) {
        //         // flash a message
        //         $this->container->flash->addMessage('snackbar',$e->getMessage());
        //         return $response->withRedirect($this->container->router->pathFor('dashboard.account.edit',['id'=>$id]));
        //     }

        //     $filename = $fileUploadService->moveUploadedFileTo( FileUploadService::UPLOADS_PATH );

        //     $storageService = new StorageService($account);
        //     $uploadedSrcFile = FileUploadService::UPLOADS_PATH . DIRECTORY_SEPARATOR . $filename;
        //     $moveTo = $storageService->getImageStoragePath();
        //     $srcFile = $storageService->moveFile( $uploadedSrcFile, $moveTo );
            
        //     $avatar = AvatarService::retrieveBy('id_account',$account->id);
        //     if (!$avatar) {
        //         $avatar = new AvatarModel;
        //         $avatar->id_account = $account->id;
        //         $avatar = AvatarService::create($avatar);
        //     }

        //     // salvar para remover no futuro
        //     $oldAvatar = $avatar->src_file;

        //     $avatar->file = $filename;
        //     $avatar->src_file = $srcFile;
        //     AvatarService::update($avatar);

        //     $avatar->cropImage(); // crop image

        //     // remover o arquivo antigo
        //     StorageService::deleteFile($oldAvatar);

        //     $this->container->flash->addMessage('snackbar','Arquivo enviado com sucesso');
        //     return $response->withRedirect($this->container->router->pathFor('dashboard.account.edit',['id'=>$id]));
        // }

        // public function adminAvatarDelete($request, $response, $args) {
        //     // obtem a conta alvo
        //     $id = $args['id'];   
        //     $account = AccountService::retrieve($id);
        //     if(!$account) {
        //         // flash a message
        //         $this->container->flash->addMessage('snackbar','Falha ao obter a conta alvo');
        //         return $response->withRedirect($this->container->router->pathFor('dashboard'));
        //     }
            
        //     // obtem o avatar da conta
        //     $avatar = AvatarService::retrieveBy('id_account',$account->id);
        //     if (!$avatar)
        //         return $response->withRedirect($this->container->router->pathFor('dashboard.account.edit',['id'=>$id]));    
            
        //     StorageService::deleteFile($avatar->src_file);
        //     AvatarService::delete($avatar->id);

        //     $this->container->flash->addMessage('snackbar','Arquivo removido com sucesso');
        //     return $response->withRedirect($this->container->router->pathFor('dashboard.account.edit',['id'=>$id]));
        //     die('delete');
        // }
        
    }