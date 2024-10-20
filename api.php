<?php
    session_start();

    if (!isset($_SESSION['foret'])) {
        $config = json_decode(file_get_contents('config.json'), true);
        $hauteur = $config['hauteur'];
        $largeur = $config['largeur'];
        $positions_feu = $config['positions_feu'];
        $probabilite = $config['probabilite'];

        // Initialiser la forêt
        $foret = array_fill(0, $hauteur, array_fill(0, $largeur, 0));

        // Mettre le feu aux positions initiales
        foreach ($positions_feu as $position) {
            $foret[$position[0]][$position[1]] = 1; // 1 = en feu
        }

        $nouvelleGrille = $foret;
        $_SESSION['foret'] = $foret;
        $_SESSION['probabilite'] = $probabilite;
        $_SESSION['feu'] = true;
    } else {
        $foret = $_SESSION['foret'];
        $probabilite = $_SESSION['probabilite'];

        // Si le feu est éteint, renvoyer l'état actuel
        if (!$_SESSION['feu']) {
            echo json_encode($foret);
            exit;
        }

        $nouvelleGrille = $foret;
        $aBrule = false;

        // Simuler la propagation du feu
        for ($i = 0; $i < count($foret); $i++) {
            for ($j = 0; $j < count($foret[$i]); $j++) {
                if ($foret[$i][$j] == 1) {
                    $nouvelleGrille[$i][$j] = 2; // La case devient cendre

                    // Vérifier les cases adjacentes
                    foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as $direction) {
                        $ni = $i + $direction[0];
                        $nj = $j + $direction[1];

                        // Vérifier les limites de la grille
                        if ($ni >= 0 && $ni < count($foret) && $nj >= 0 && $nj < count($foret[$i])) {
                            if ($foret[$ni][$nj] == 0 && rand(0, 100) / 100 < $probabilite) {
                                $nouvelleGrille[$ni][$nj] = 1; // Propagation du feu
                                $aBrule = true;
                            }
                        }
                    }
                }
            }
        }

        $_SESSION['foret'] = $nouvelleGrille;
        $_SESSION['feu'] = $aBrule;

    }

    if(!$_SESSION['feu']){
        session_destroy();
    }

// Retourner l'état de la grille en JSON
    echo json_encode($nouvelleGrille);
