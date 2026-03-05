<?php
require_once(__DIR__ . "/../model/Imoveis.php");
require_once(__DIR__ . "/../model/FotoImovel.php"); // Importante para salvar as fotos

function criarSlug($titulo) {
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $titulo)));
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $imovel = new Imovel(
            id: 0,
            titulo:               $_POST['titulo'] ?? '',
            tipo:                 $_POST['tipo'] ?? '',
            tipo_negocio:         $_POST['tipo_negocio'] ?? '',
            descricao:            $_POST['descricao'] ?? '',
            preco:                (float)($_POST['preco'] ?? 0),
            valor_condominio:     (float)($_POST['valor_condominio'] ?? 0),
            valor_iptu:           (float)($_POST['valor_iptu'] ?? 0),
            cep:                  $_POST['cep'] ?? '',
            cidade:               $_POST['cidade'] ?? '',
            bairro:               $_POST['bairro'] ?? '',
            estado:               $_POST['estado'] ?? '',
            endereco:             $_POST['endereco'] ?? '',
            quartos:              (int)($_POST['quartos'] ?? 0),
            banheiros:            (int)($_POST['banheiros'] ?? 0),
            vagas:                (int)($_POST['vagas'] ?? 0),
            area:                 (float)($_POST['area'] ?? 0),
            status:               $_POST['status'] ?? 'disponivel',
            id_corretor:          (int)($_POST['id_corretor'] ?? 1),
            possui_piscina:       isset($_POST['possui_piscina']),
            possui_churrasqueira: isset($_POST['possui_churrasqueira']),
            slug:                 criarSlug($_POST['titulo'] ?? 'imovel')
        );

        // 1. Salva o imóvel primeiro para gerar o ID
        if ($imovel->salvar()) {
            $idImovel = $imovel->id; 

            // 2. Processa o upload das fotos enviadas pelo formulário
            if (isset($_FILES['fotos']) && !empty($_FILES['fotos']['name'][0])) {
                $pastaDestino = "../uploads/imoveis/";
                
                if (!is_dir($pastaDestino)) {
                    mkdir($pastaDestino, 0777, true);
                }

                foreach ($_FILES['fotos']['name'] as $key => $nomeOriginal) {
                    $extensao = pathinfo($nomeOriginal, PATHINFO_EXTENSION);
                    $nomeArquivo = uniqid() . "." . $extensao;
                    $caminhoFisico = $pastaDestino . $nomeArquivo;

                    if (move_uploaded_file($_FILES['fotos']['tmp_name'][$key], $caminhoFisico)) {
                        // Verifica qual é a foto de destaque escolhida
                        $destaque = (isset($_POST['index_principal']) && $_POST['index_principal'] == $key);

                        // 3. Instancia e salva cada foto na tabela fotos_imovel
                        $foto = new FotoImovel(
                            id_imovel: $idImovel,
                            caminho:   "uploads/imoveis/" . $nomeArquivo,
                            destaque:  $destaque,
                            ordem:     $key
                        );
                        $foto->salvar();
                    }
                }
            }

            // Redirecionamento corrigido para evitar erro 404
            header("Location: ../view/painelCadImoveis.php?sucesso=1");
            exit;
        }
    } catch (Exception $e) {
        die("Erro: " . $e->getMessage());
    }
}