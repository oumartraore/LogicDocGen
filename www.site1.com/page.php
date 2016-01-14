<!DOCTYPE>
<html>
	<head>
		<title> Bonjour </title>
	</head>

	<body>
		<?php
			// Chemin vers le dossier de Téléchargement. 
			$folder = "UploadDirectory";
			
			// On met les droits sur le Dossier
			chmod($folder, 777);

			// On teste d'abord le fichier Télécharger pour voir si il n'y a pas d'erreur.
			if($_FILES["file"]["error"] > 0)
			{
				echo "Error";
			}
			else {
				// Si il y a pas d'erreur on recupère l'extension du fichier

				$temp = explode(".", $_FILES["file"]["name"]);
				$extension = end($temp);

				// On declare un tableau avec les extensions possibles pour le projet (txt, cpp)
				$allowExtension = array("txt", "cpp");

				// On verifie que le fichier à l'upload est bien autoriser
				if(in_array($extension, $allowExtension))
				{
					// On n'est sure que l'on a un fichier cpp ou un fichier txt.
					$fileName = $_FILES["file"]["name"];

					// Cett variable sera le chemin ver notre fichier à lire pour le convertir.
					$pathFile = $folder . "/" . $fileName;

					//On ouvre le fichier intermédiare  $pathFile.php 
					$myFileOutput = fopen($folder . "/render.php", "w");
					
					// On crée l'entete php pour la convertion en pdf (Plus trad on le présentera sous forme de lien sur le site)
					fwrite($myFileOutput, "<?php \n");

					// On inclut la librairie FPDF (www.fpdf.org - Licence : )
					fwrite($myFileOutput, "require('../fpdf/fpdf.php');" . "\n");
					// On instance le constructeur de la classe
                                        fwrite($myFileOutput, "\t\$pdf = new FPDF();" . "\n");
					// On ajoute une page vierge
					fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");
	

					// Determination du type de l'extension (Pour fonction selon le cas)
                                        if($extension == "txt")
                                                $choix = 1;
                                        else
                                                $choix = 2;
	
					switch($choix)
					{
						case 1: // Generation Flat file documentation
							// On n'applique une police differente par defaut Arial 12
                                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

							if(move_uploaded_file($_FILES["file"]["tmp_name"], $pathFile)){
                                                        	// On met les droits sur le fichier
                                                                chmod($pathFile, 777);
								
								// On ferme d'abord le fichier avec l'entete page vide.
								fclose($myFileOutput);

								flatFileDocumentation($pathFile, $fileName);
							}
							break;
						case 2: // Generation Source code file documentation
							
							$fileWeight = $_FILES["file"]["size"]; // Pour obtenir la taille en KB

							// On n'applique une police differente 
							fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"B\", 14);" ."\n");

							if(move_uploaded_file($_FILES["file"]["tmp_name"], $pathFile)){
								// On met les droits sur le fichier
								chmod($pathFile, 777);

								fwrite($myFileOutput, "\t\$pdf->Cell(0,0,\"File name :  $fileName\",0,0,C);\n");
								fwrite($myFileOutput, "\t\$pdf->Text(10,30,\"File weight : $fileWeight B\");\n");

								fclose($myFileOutput);

								sourceCodeDocumentation($pathFile, $fileName);
							}else {
								// On affiche une erreur et on lève une Exception
							}
														
							break;
					}	
				} // On ne fait rien on redirige avec un message d'erreur sur l'extension
				else {
					echo "Extension non valide <br />";
				}
			}

			// Function de Traitement (Flat File Document & Source Code Document)

			// 1ere Partie Traitement de Flat Document

			function flatFileDocumentation($pathR, $fName){
				$myFile = fopen($pathR, "r");
				$myFileOutput = fopen("UploadDirectory/render.php", "a");


				// Pattern particulier qui ne peuvent se retrouver que sur une seule ligne.	
				$regexBlankPageText = "#\*\*/\s?(.+)\s?/\*\*#i";
				$regexLevel1Title = "#\*1/\s?(.+)\s?/1\*#i";
				$regexLevel2Title = "#\*2/\s?(.+)\s?/2\*#i";
				$regexLevel3Title = "#\*3/\s?(.+)\s?/3\*#i";
				$regexBlankLine = "#\*\*bl\*\*#i";
                                $regexBlankPage = "#\*\*bp\*\*#i";
                                $regexTableContent = "#\*\*tbl\*\*#i";

				// Pattern dans le texte de coloration sytaxique
				$regexBlod = "#(.*)b/\s?(.+)\s?/b(.*)#";
				$regexItalic = "#(.*)i/\s?(.+)\s?/i(.*)#";
				$regexUnderline = "#(.*)u/\s?(.+)\s?/u(.*)#";
				
				$regexBlodItalic = "#(.*)bi/\s(.+)\s?/bi(.*)#";
				$regexBlodUnderline = "#(.*)bu/\s(.+)\s/bu(.*)#";
				$regexBlodItalicUnderline = "#(.*)biu/\s(.+)\s/(.*)biu#";
				$regexColor = "#color:\#[1-9A-F]{1,5}/\s?(.+)\s?/:color#i";

				// Création du pattern final avec une regexPattern (Ok - Je la joue cool).
				$regexText = "#()    #i";

				$cordX = 10;
				$cordY = 0;

				$noRegex = 0;
				
				// Creation de 2 Tableau pour contenir **tbl**
				$tableContentString = array();
				$tableContentLevel = array();

				if(!$myFile || !$myFileOutput){
					echo "Impossible de lire le fichier <br />";
				}else {
					// On recupère la table of content.
					echo "Tu es au sommaire <br />";
					fwrite($myFileOutput, "\t\$pdf->Cell(0,0,\"SOMMAIRE\",0,0,C);\n");

					$cordY = 20;
					while(!feof($myFile)){
						$line = fgets($myFile);
						if(preg_match($regexLevel1Title, $line, $resultat)){
							$cordX = 10;
							$cordY = $cordY + 5;

							
							fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");
							array_push($tableContentString, $resultat[1]);
							array_push($tableContentLevel, 1);
						}
						if(preg_match($regexLevel2Title, $line, $resultat)){
							$cordX = 20;
							$cordY = $cordY + 5;
				
							fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");
							array_push($tableContentString, $resultat[1]);
                                                        array_push($tableContentLevel, 2);
						}
						if(preg_match($regexLevel3Title, $line, $resultat)){
                                                        $cordX = 30;
                                                        $cordY = $cordY + 5;

                                                        fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");
							array_push($tableContentString, $resultat[1]);
                                                        array_push($tableContentLevel, 3);
                                                }
					}
					
					// On ajoute une page vierge
                                        fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");

					// On recupère les Patterns Particuliers - Mais avant on se repositionne au debut du fichier.
					//fseek($myFile, 0, SEEK_SET);
					rewind($myFile);

					$cordY = 20;					

					while(!feof($myFile)){
						$line = fgets($myFile);
						$noRegex = 0;

						// Detection du pattern Particulier qui se retrouve sur une ligne.
						if(preg_match($regexBlankPageText, $line, $resultat)){
							// Changement de la police
							fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"B\", 32);" ."\n");
							$cordY = $cordY + 10;
							fwrite($myFileOutput, "\t\$pdf->SetXY(0, $cordY);" ."\n");
							fwrite($myFileOutput, "\t\$pdf->Cell(0,0,\"$resultat[1]\",0,0,C);\n");
							// On remet la police par defaut.
							$cordY = $cordY + 10;
							fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");	

							$noRegex = 1;
						}
					
						if(preg_match($regexLevel1Title, $line, $resultat)){
							$cordX = 10;
                                                        $cordY = $cordY + 5;
							
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(130, 0, 0);\n");
                                                        fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(0);\n");

							$noRegex = 1;
						}

						if(preg_match($regexLevel2Title, $line, $resultat)){
							$cordX = 20;
                                                        $cordY = $cordY + 5;
	
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(0, 130, 0);\n");
                                                        fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(0);\n");

							$noRegex = 1;
						}

						if(preg_match($regexLevel3Title, $line, $resultat)){
							$cordX = 30;
                                                        $cordY = $cordY + 5;

							fwrite($myFileOutput, "\t\$pdf->SetTextColor(20, 20, 130);\n");
                                                        fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(0);\n");

							$noRegex = 1;
						}

						if(preg_match($regexBlankLine, $line)){
							$cordY = $cordY + 5;
							$noRegex = 1;
                                                }

                                                if(preg_match($regexBlankPage, $line)){
							// On ajoute 2 pages vierges (Une pour le pattern et l'autre ou on va commencer a ecrire
                                        		fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");
                                        		fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");
							$cordY = 20;
							$noRegex = 1;
                                                }

						if(preg_match($regexTableContent, $line)){
							if($cordY > 20){
								$cordY = 20;
								// On ajoute une page vierge
                                        			fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");
							}

							fwrite($myFileOutput, "\t\$pdf->SetXY(0, $cordY);" ."\n");
                                                        fwrite($myFileOutput, "\t\$pdf->Cell(0,0,\"Table of Content\",0,0,C);\n");
							$cordY = $cordY + 10;
		
							for($i = 0; $i < count($tableContentLevel); $i++){
								switch($tableContentLevel[$i]){
									case 1:
										$cordY = $cordY + 5;
										$cordX = 10;
                                                        			fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$tableContentString[$i]\");\n");
										break;
									case 2:
										$cordY = $cordY + 5;
                                                                                $cordX = 20;
                                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$tableContentString[$i]\");\n");
										break;
									case 3: 
										$cordY = $cordY + 5;
                                                                                $cordX = 30;
                                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$tableContentString[$i]\");\n");
										break;
								}
							}
							$cordY = 20;
                                                        // On ajoute une page vierge
                                                        fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");
							$noRegex = 1;
                                                }
						
						if(preg_match($regexBlod, $line, $resultat)){
							//echo "Voila resultat[0] : " . $resultat[0] . "<br />";
							// On se positionne sur la bonne ligne. et au debut
							$cordX = 10;
							$cordY = $cordY + 5;
							fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

							// On print ce qu'il Y a avant.
							
							if($resultat[1] != ""){
								fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                                        	// On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                                        	fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                                        	fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

								// On met la chaine en Gras
                                                        	fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"B\", 12);" ."\n");
                                                        	fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                                        	// On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                        	fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");
								
							}else{
								// On met la chaine en Gras
                                                        	fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"B\", 12);" ."\n");
                                                        	fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
							}

							// On remet la police par defaut avant de quitter et printer le reste de la chaine.
							fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

							if($resultat[3] != ""){
								fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                                        	fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
							}
							$noRegex = 1;
						}

						if(preg_match($regexItalic, $line, $resultat)){
							// On se positionne sur la bonne ligne. et au debut
                                                        $cordX = 10;
                                                        $cordY = $cordY + 5;
                                                        fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

                                                        // On print ce qu'il Y a avant.

                                                        if($resultat[1] != ""){
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                                                // On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"I\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                                                // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        }else{
                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"I\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
                                                        }

                                                        // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        if($resultat[3] != ""){
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
                                                        }
							$noRegex = 1;
						}
	
						if(preg_match($regexUnderline, $line, $resultat)){
							// On se positionne sur la bonne ligne. et au debut
                                                        $cordX = 10;
                                                        $cordY = $cordY + 5;
                                                        fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

                                                        // On print ce qu'il Y a avant.

                                                        if($resultat[1] != ""){
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                                                // On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"U\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                                                // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        }else{
                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"U\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
                                                        }

                                                        // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        if($resultat[3] != ""){
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
                                                        }
							$noRegex = 1;
						}

						if(preg_match($regexBlodItalic, $line, $resultat)){
							// On se positionne sur la bonne ligne. et au debut
                                                        $cordX = 10;
                                                        $cordY = $cordY + 5;
                                                        fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

                                                        // On print ce qu'il Y a avant.

                                                        if($resultat[1] != ""){
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                                                // On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"BI\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                                                // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        }else{
                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"BI\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
                                                        }

                                                        // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        if($resultat[3] != ""){
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
                                                        }
							$noRegex = 1;
						}

						if(preg_match($regexBlodUnderline, $line, $resultat)){
							// On se positionne sur la bonne ligne. et au debut
                                                        $cordX = 10;
                                                        $cordY = $cordY + 5;
                                                        fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

                                                        // On print ce qu'il Y a avant.

                                                        if($resultat[1] != ""){
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                                                // On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"BU\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                                                // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        }else{
                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"BU\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
                                                        }

                                                        // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        if($resultat[3] != ""){
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
                                                        }
							$noRegex = 1;
						}

						if(preg_match($regexBlodItalicUnderline, $line, $resultat)){
							// On se positionne sur la bonne ligne. et au debut
                                                        $cordX = 10;
                                                        $cordY = $cordY + 5;
                                                        fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

                                                        // On print ce qu'il Y a avant.

                                                        if($resultat[1] != ""){
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                                                // On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"BIU\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                                                // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        }else{
                                                                // On met la chaine en Gras
                                                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"BIU\", 12);" ."\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
                                                        }

                                                        // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                                        if($resultat[3] != ""){
                                                                fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                                                fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
                                                        }
							$noRegex = 1;
						}

						if(preg_match($regexColor, $line)){
						
						}
						
						if($noRegex == 0){
							// On se positionne sur la bonne ligne. et au debut
                                                        $cordX = 10;
                                                        $cordY = $cordY + 5;

							// On ecrit $ligne
							fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$line\");\n");
						}
					}
					fwrite($myFileOutput, "\t\$pdf->Output();\n");
                                        fwrite($myFileOutput, "?>");
                                        fclose($myFile);
                                        fclose($myFileOutput);
				}
			}


			// 2nd Partie Traitement de Source Code Documentation dans un format intermediare (Txt)
			
			function sourceCodeDocumentation($pathR, $fName){
				echo "My File is " . $pathR . "<br />";

				$myFile = fopen($pathR, "r");
				
				// Fichier de Documentation du Source code ".cpp"
				$myFileOutput = fopen("UploadDirectory/render.php", "a+");
				$lineCount = 0;	
				$cordX = 10;
				$cordY = 30;
				$numberFunction = 0;

				$regexNamespace = "#using namespace (.+)#i";			

				if(!$myFile && !$myFileOutput){
					echo "Impossible de lire le fichier <br />";
				}else {
					echo "Tout va bien <br />";
					
					while(!feof($myFile)){
						$line = fgets($myFile);
						$lineCount++;
	
						// Detection du namespace grace aux regex
						if(preg_match($regexNamespace, $line, $resultat)){
							// Si on trouve un nameSpace On le rajoute au fichier
							$cordY = updateCordY($cordY);
							fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[0]\");\n");
						}

						// Nous allons utiliser cette regex pour trouver les patterns des fonctions
						$regexFunction = "#(int[^f]|float|char|bool|double)\s?(.+)\s?(\(.+\))#i";

						if(preg_match($regexFunction, $line, $resultat)){
							$cordY = updateCordY($cordY);
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(130, 0, 0);\n");
							fwrite($myFileOutput, "\t\$pdf->Text($cordX+20,$cordY,\"$numberFunction - Function : $resultat[0]\");\n");
							$cordY = updateCordY($cordY);
							fwrite($myFileOutput, "\t\$pdf->SetTextColor(0);\n");
							fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"Return Type : $resultat[1]\");\n");
							$cordY = updateCordY($cordY);
							fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"Argument Type & Name : $resultat[3]\");\n");
							

							$numberFunction++;
							echo "Resultat 2 : " . $resultat[2] . "<br />";
							echo "Resultat 3 : " . $resultat[3] . "<br />";
						}
					}

					$cordY = updateCordY($cordY);
					fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"Line Count : $lineCount\");\n");
					$cordY = updateCordY($cordY); // (Pour de la documentation de code j'aurai pu faire $cordY+10)
					fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"Number of Function : $numberFunction\");\n");

	
					$cordY = 260;
					fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"Fin de Page\");\n");


					fwrite($myFileOutput, "\t\$pdf->AddPage();" .  "\n");

					fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"Fin de Page\");\n");
					
					fwrite($myFileOutput, "\t\$pdf->Output();\n");
					fwrite($myFileOutput, "?>");

					
					

					fclose($myFile);
					fclose($myFileOutput);
				}
			}
		
			function updateCordY($cordY){
				if($cordY >= 260)
					return 10;
				else
					return $cordY + 10;
			}
	
			/*function putStyleFound($resultat, $style){
				//echo "Voila resultat[0] : " . $resultat[0] . "<br />";
                                // On se positionne sur la bonne ligne. et au debut
                                $cordX = 10;
                                $cordY = $cordY + 5;
                                fwrite($myFileOutput, "\t\$pdf->SetXY($cordX, $cordY);\n");

                                // On print ce qu'il Y a avant.

                                if($resultat[1] != ""){
                                	fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[1]\");\n");

                                        // On se decale de la longeur de la premiere chaine. (Qui n'est pas du blod) Dans le code du fichier.
                                        fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[1]\"));\n");
                                       	fwrite($myFileOutput, "\t\$cordX = \$pdf->GetX() + 9;\n");

                                        // On met la chaine en Gras
                                       	fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"$style\", 12);" ."\n");
                                        fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[2]\");\n");

                                        // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                               	}else{
                                  	// On met la chaine en Gras
                                        fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"$style\", 12);" ."\n");
                                        fwrite($myFileOutput, "\t\$pdf->Text($cordX,$cordY,\"$resultat[2]\");\n");
                                }

                                // On remet la police par defaut avant de quitter et printer le reste de la chaine.
                                fwrite($myFileOutput, "\t\$pdf->SetFont(\"Arial\", \"\", 12);" ."\n");

                                if($resultat[3] != ""){
                                	fwrite($myFileOutput, "\t\$cordX = \$pdf->SetX(\$pdf->GetStringWidth(\"$resultat[2]\")) + 18 + \$cordX;\n");
                                        fwrite($myFileOutput, "\t\$pdf->Text(\$cordX,$cordY,\"$resultat[3]\");\n");
                                }

			}*/
			header('Location: index.html');
		?>
	</body>
</html>		
