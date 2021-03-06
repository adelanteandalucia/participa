<?php

if (isset($c)) {
    call_user_func(callFuncionParametros($c));
} else {
    call_user_func('patios');
}

function programa_off() {
    global $html;

    $html->asignar("msg", "Estamos ajustando la página. Volvemos en unos minutos.");
    $html->plantilla("error.tpl");
    $html->ver();
}

function programa() {
    global $html;

    $html->asignar("version", "default");

    $cats = new Programa_Categoria_Controladora();

    $res = $cats->getCategorias();
    $lista = $cats->getLista($res);
    $divs = $cats->getDIVS($res);

    $html->asignar("ip", getIPv4());
    $html->asignar("programa", $divs);
    $html->plantilla("index.tpl");
    $html->ver();
}

function fusion() {
    global $html, $codigoHTML,$op;
    $codigoHTML = "";

    if (isset($op) && !is_null($op)){
        $cat = new Programa_Categoria($op);
        $codigoHTML .= mostrarCategoria($cat);
    } else {
        $cats = new Programa_Categoria_Controladora();
        $categorias = $cats->getCategorias(false);
        foreach ($categorias as $categoria) {
            $codigoHTML .= mostrarCategoria($categoria);
        }        
    }

    $html->asignar("codigoHTML", $codigoHTML);
    $html->plantilla("html.tpl");
    $html->ver(CONF_HOME . "tpl/blanco.tpl");
}

function mostrarCategoria($categoria, $nivel = 1) {
    $html = "";
    $html .= espaciado($nivel);
    $html .= "<!-- START " . $categoria->id . " -->\n";
    $html .= espaciado($nivel);
    $html .= "<div class='fusion nivel-$nivel'>\n";
    $html .= espaciado($nivel);
    $tag = "<p class='titulo ml-$nivel' >";
    $tagclose = "</p>";


    $html .= $tag . $categoria->id . " - " . $categoria->nombre . $tagclose . "\n";

    //PARRAFOS
    $html .= mostrarParrafos($categoria->id, 'intro', $nivel);
    
    //PROPUESTAS
    $html .= mostrarParrafos($categoria->id, 'propuesta', $nivel);
    
    //ENMIENDAS ADICION
    $html .= mostrarEnmiendasAdicion($categoria->id, $nivel);

    if (isset($categoria->hijos)) {
        $nivel++;
        foreach ($categoria->hijos as $hijo) {
            $html .= mostrarCategoria($hijo,  $nivel);
        }
    }

    $html .= espaciado($nivel);
    $html .= "</div>\n";
    $html .= espaciado($nivel);
    $html .= "<!-- END " . $categoria->id . " -->\n";

    return $html;
}

function mostrarParrafos($categoria, $tipo, $nivel = 1) {
    global $db;
    $html = "";
    $parrafos = new Programa_Propuesta_Controladora();
    $parrafos->enmiendas = false;
    $parrafos = $parrafos->getPropuestasCategoria($categoria, $tipo);
    $html .= espaciado($nivel);
    $html.="<div class='ml-$nivel'>\n";
    foreach ($parrafos as $parrafo) {

        $enmiendasAlIntro = new Programa_Enmienda_Controladora();
        $enmiendasAlIntro->estado = 1;
        $enmiendasAlIntro->definitivo = 1;
        $tmp = $enmiendasAlIntro->getEnmiendasFrom($parrafo->id, 'idPropuesta');
        $parrafo->enmiendas = $tmp;
        //var_dump($tmp);        
        if ((count($parrafo->enmiendas)) == 0){
            $html .= espaciado($nivel);            
            $html .= "<p class='tipo-$tipo'><span class='badge badge-default' style='color:green'>" . (($parrafo->tipo=='propuesta')?"Propuesta":"Párrafo") . " ". $parrafo->id . "</span> " . preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $parrafo->texto) . "</p>\n";
            $muestraOriginal = false;
        }
        $muestraOriginal = true;
        $i=1;
        foreach ($parrafo->enmiendas as $enmienda) {
            if ($muestraOriginal) {
                $html .= espaciado($nivel);
                $html .= "<p class='tipo-$tipo original'><span class='badge badge-default' style='color:red'>" . (($parrafo->tipo=='propuesta')?"Propuesta":"Párrafo") . " ". $parrafo->id . "</span>  " . $parrafo->texto . "</p>\n";
                $muestraOriginal = false;
            }
            $html .= espaciado($nivel);
            $html .= "<p class='tipo-$tipo enmienda'><span class='badge badge-default' style='color:orange'>" . (($parrafo->tipo=='propuesta')?"Propuesta":"Párrafo") . " ". $parrafo->id ." (Enmienda #". $enmienda->id++ . " - ".$enmienda->tipo.")</span> " . preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $enmienda->redaccion) . "</p>\n";
        }        
    }
    $html .= espaciado($nivel);
    $html.="</div>\n";
    return $html;
}

function mostrarEnmiendasAdicion ($categoria, $nivel = 1) {
    $html = "";
    $adicion = new Programa_Enmienda_Controladora();
    $adicion->estado = 1;
    $adicion->definitivo = 1;
    $tmp = $adicion->getEnmiendasFrom($categoria, 'idCategoria');
    $enmiendas = $tmp;
    //var_dump($tmp);  
    $html .= espaciado($nivel);
    $html.="<div class='ml-$nivel'>\n";    
    foreach ($enmiendas as $enmienda) {
        $html .= espaciado($nivel);
        $html .= "<p class='tipo-$enmienda->tipo  enmienda'><span class='badge badge-default' style='color:blue'>Enmienda ADD #". $enmienda->id++ . " - ".$enmienda->tipo."</span> " . preg_replace("/<([a-z][a-z0-9]*)[^>]*?(\/?)>/i",'<$1$2>', $enmienda->redaccion) . "</p>\n";
    }        
    $html .= espaciado($nivel);
    $html.="</div>\n";    
    return $html;    
}

function espaciado($nivel) {
    $html = "";
    for ($i = 0; $i < $nivel; $i++) {
        $html .= "\t";
    }

    return $html;
}

function formulario() {
    global $html, $op, $subop;

    $html->asignar("version", "patios");
    $html->asignar("ip", getIPv4());

    if (isset($_GET['colectivos']) && $_GET['colectivos'] == 1) {
        $colectivos = true;
    } else {
        $colectivos = false;
    }


    switch ($op) {
        case 'add':
        case 'sup':
        case 'mod':
            $accion = $op;
            break;
        default:
            $html->asignar("msg", "No existe la acción indicada");
            $html->plantilla("error.tpl");
            $html->ver();
            exit;
    }

    $elementotipo = 'propuesta';
    if ($accion == 'add') {
        $categoria = new Programa_Categoria($subop);
        if (!($categoria->existe)) {
            $html->asignar("msg", "No existe la categoría indicada");
            $html->plantilla("error.tpl");
            $html->ver();
            exit;
        }
        $html->asignar("categoria", $categoria);
    }

    if ($accion == 'mod' || $accion == 'sup') {
        $propuesta = new Programa_Propuesta($subop);
        if (!$propuesta->existe) {
            $html->asignar("msg", "No existe la propuesta indicada");
            $html->plantilla("error.tpl");
            $html->ver();
            exit;
        }
        $html->asignar("elemento", $propuesta);
        if ($propuesta->tipo == 'propuesta') {
            $elementotipo = 'propuesta';
        } else {
            $elementotipo = 'párrafo';
        }
    }

    $html->asignar("op", $op);
    if (isset($_SERVER['HTTP_REFERER'])) {
        $url_anterior = $_SERVER['HTTP_REFERER'];
    } else {
        $url_anterior = "javascript:window.history.back();";
    }
    $html->asignar("url_anterior", $url_anterior);
    $html->asignar("colectivos", $colectivos);
    $html->asignar("elementotipo", $elementotipo);
    $html->asignar("accion", $accion);

    if ($colectivos) {
        $html->plantilla("formulario.tpl");
        //$html->plantilla("fuera_de_plazo.tpl");
    } else {
        $html->plantilla("fuera_de_plazo.tpl");
    }
    $html->ver();
}

function propuesta($pisaOP = NULL, $tipo = 'Propuesta', $ver = true) {
    global $op, $html;

    $html->asignar("version", "patios");
    $html->asignar("tipo", $tipo);

    if ($pisaOP) {
        $op = $pisaOP;
    }

    $propuesta = new Programa_Propuesta($op);

    if (!$propuesta->existe) {
        $html->asignar("msg", "No existe la propuesta indicada");
        $html->plantilla("error.tpl");
        if ($ver) {
            $html->ver();
        }
        return 0;
    }

    if ($propuesta->tipo == 'propuesta') {
        $elementotipo = 'propuesta';
    } else {
        $elementotipo = 'párrafo';
    }


    if (isset($_GET['colectivos']) && $_GET['colectivos'] == 1) {
        $colectivos = true;
    } else {
        $colectivos = false;
    }

    $tienePadre = true;
    $padres = array();
    $categoria = $propuesta->cat;
    while ($tienePadre) {
        //var_dump($categoria);
        if (isset($categoria->padre->id)) {
            $padres[] = $categoria->padre;
            $categoria = $categoria->padre;
        } else {
            $tienePadre = false;
        }
    }
    $padres = array_reverse($padres);
    //var_dump($padres);
    //var_dump($propuesta);

    $in = strip_tags($propuesta->texto);
    $propuesta->textoplano = Html2Text\Html2Text::convert($propuesta->texto);
    $propuesta->textoplano = preg_replace("/\r|\n/", "", $propuesta->textoplano);
    $propuesta->acortada = strlen($in) > 220 ? substr($in, 0, 220) . "..." : $in;
    $html->asignar("ip", getIPv4());
    $html->asignar("elementotipo", $elementotipo);
    if (isset($_SERVER['HTTP_REFERER'])) {
        $url_anterior = $_SERVER['HTTP_REFERER'];
    } else {
        $url_anterior = "javascript:window.history.back();";
    }
    $html->asignar("url_anterior", $url_anterior);
    $html->asignar("propuesta", $propuesta);
    $html->asignar("actual", $propuesta->cat);
    $html->asignar("padres", $padres);
    $html->asignar("colectivos", $colectivos);
    $html->titulo('Propuesta ' . $propuesta->id . CONF_TITULOPAGINA_POS);
    $html->descripcion($propuesta->texto);
    $propuesta->getEnmiendas();
    $html->plantilla("propuesta.tpl");
    if ($ver) {
        $html->ver();
    }
}

function categoria() {
    global $html, $c, $op;

    $html->asignar("version", "patios");

    if (isset($c[1])) {
        $id = $c[1];
    } else {
        $id = 0;
    }

    if (isset($_GET['colectivos']) && $_GET['colectivos'] == 1) {
        $colectivos = true;
    } else {
        $colectivos = false;
    }

    $tienePadre = true;
    $padres = array();
    $categoria = new Programa_Categoria($op);
    while ($tienePadre) {
        //var_dump($categoria);
        if (isset($categoria->padre->id)) {
            $padres[] = $categoria->padre;
            $categoria = $categoria->padre;
        } else {
            $tienePadre = false;
        }
    }


    if (isset($_GET['colectivos']) && $_GET['colectivos'] == 1) {
        $colectivos = true;
    } else {
        $colectivos = false;
    }

    $categoria = new Programa_Categoria($op);
    $categoria->getIntro();
    $padres = array_reverse($padres);

    if (isset(end($padres)->id)) {
        $anterior = end($padres)->id;
    } else {
        $anterior = 'ROOT';
    }
    $hijos = new Programa_Categoria_Controladora();
    $propuestas = new Programa_Propuesta_Controladora();
    $propuestas = $propuestas->getPropuestasCategoria($categoria->id);
    $html->asignar("ip", getIPv4());
    $html->asignar("categoria", $categoria);
    $html->asignar("propuestas", $propuestas);
    $html->asignar("actual", $categoria);
    $html->asignar("colectivos", $colectivos);
    $html->asignar("anterior", $anterior);
    $html->asignar("padres", $padres);
    $html->asignar("hijos", $hijos->getNivel($categoria->id));
    $categoria->getEnmiendas();
    $html->plantilla("nivel.tpl");
    $html->ver();
}

function patios() {
    categoria();
}

function parrafo() {
    propuesta(null, 'Párrafo', true);
}

function patios_listado() {
    global $html;
    $html->asignar("version", "patios");
    $html->titulo('PATIOS PROVINCIALES - Inscríbete');
    $html->descripcion('En los Patios Provinciales discutiremos las enmiendas recibidas y daremos forma al programa de Adelante Andalucía');
    $html->plantilla("patios_listado.tpl");
    $html->ver();
}

function pdf() {
    echo "Disponible próximamente.";
}

function enviar() {
    global $db;


    if ($_FILES) {
        $_POST['fichero'] = $_FILES; //añadimos a POST lo enviado por FILES
    } else {
        $_POST['fichero'] = null;
    }

    $id = new Programa_Enmienda($_POST);

    if ($id) {

        $email = new Correo();
        $html = "";

        switch ($id->tipo) {
            case 'sup': $tipo = "SUPRESIÓN " . $id->idPropuesta->id;
                break;
            case 'add': $tipo = "ADICIÓN";
                break;
            case 'mod': $tipo = "MODIFICACIÓN " . $id->idPropuesta->id;
                break;
        }

        $html .= "<strong>ID ENMIENDA: </strong>" . $id->id . "<br>";
        $html .= "<strong>TIPO: </strong>" . $tipo . "<br>";
        $html .= "<strong>PROPUESTA Nº: </strong>" . $id->idPropuesta->id . "<br>";
        $html .= "<strong>CATEGORIA: </strong>" . $id->idCategoria->id . "<br>";
        $html .= "<strong>CATEGORIA NOMBRE: </strong>" . $id->idCategoria->nombre . "<br>";
        $html .= "<strong>NOMBRE: </strong>" . $id->nombre . "<br>";
        $html .= "<strong>APELLIDOS: </strong>" . $id->apellidos . "<br>";
        $html .= "<strong>CP: </strong>" . $id->cp->cp . "<br>";
        $html .= "<strong>EMAIL: </strong>" . $id->email . "<br>";
        $html .= "<strong>TELEFONO: </strong>" . $id->telefono . "<br>";
        $html .= "<strong>VALIDAR: </strong> <a href=" . CONF_BASEURL . "enmienda/validar/" . $id->id . "/" . $id->random . "/>Validar ahora</a><br>";
        $html .= "<strong>MOTIVACION: </strong><br>" . $id->motivacion . "<br><br>";
        $html .= "<strong>REDACCION: </strong>" . $id->redaccion . "<br>";

        if (property_exists($id->idPropuesta, 'texto')) {
            $html .= "<hr>";
            $html .= $id->idPropuesta->texto;
        }

        if (property_exists($id, 'fichero') && $id->fichero) {
            $email->adjunto = $id->fichero;
        }

        $email->fromtxt = $id->nombre . " " . $id->apellidos;
        $email->asunto = "ENMIENDA " . ($tipo);
        $email->from = MAIL_ADMIN;
        $email->to = MAIL_ENMIENDAS;
        $email->enviar($html);
        //var_dump($id);        
    }
}

function enmienda_validar() {
    global $html, $c;
    $html->asignar("version", "patios");

    $enmienda = new Programa_Enmienda($c[2]);
    if ($c[3] == $enmienda->random) {
        $id['publica'] = 1;
        $enmienda->editar($id);
        $accion = "OK";
    } else {
        $accion = "NO";
    }
    $html->asignar("accion", $accion);
    $html->plantilla("validar_enmienda.tpl");
    $html->ver();
}

function enmienda($ver = true, $pisaOP = false) {
    global $op, $html;

    if ($pisaOP) {
        $op = $pisaOP;
    }

    $html->asignar("version", "patios");

    $enmienda = new Programa_Enmienda($op);

    if (!$enmienda->existe) {
        $html->asignar("msg", "No existe la enmienda indicada");
        $html->plantilla("error.tpl");
        if ($ver) {
            $html->ver();
        }
        return 0;
    }

    if (!isset($_SESSION['ponencia']) || $_SESSION['ponencia'] != 1) {
        if ($enmienda->publica == 0) {
            $html->asignar("msg", "ERROR");
            $html->plantilla("error.tpl");
            if ($ver) {
                $html->ver();
            }
            return 0;
        }
    }


    $fichero = null;
    if ($enmienda->fichero) {
        importclass("fichero");
        $fichero = new Fichero($enmienda->fichero);
    }

    $cval = new Programa_Enmienda_Valoraciones_Controladora();
    $valoraciones = $cval->getValoraciones($enmienda->id);

    $html->asignar("fichero", $fichero);
    $html->asignar("url", CONF_BASEURL);
    $html->asignar("e", $enmienda);
    $html->titulo('Enmienda ' . $enmienda->id . CONF_TITULOPAGINA_POS);
    $html->asignar("valoraciones", $valoraciones);
    $html->plantilla("enmienda_web.tpl");
    if ($ver) {
        $html->ver();
    }
}

function enmiendas_provincias() {

    global $html;

    $provincias = array(
        "Almería" => "04",
        "Cádiz" => "11",
        "Córdoba" => "14",
        "Granada" => "18",
        "Huelva" => "21",
        "Málaga" => "29",
        "Jaén" => "23",
        "Sevilla" => "41"
    );

    $codEnmiendas = new Programa_Enmienda_Controladora();
    foreach ($provincias as $prov => $codprov) {
        $resultado = $codEnmiendas->getEnmiendasProvincia($codprov);
        $tabla[$prov] = sizeof($resultado);
    }
    $html->asignar("version", "patios");
    $html->asignar("tabla", $tabla);
    $html->plantilla("enmiendas_provincias.tpl");
    $html->ver();
}

function patio_inscripcion() {

    global $html, $c, $op, $subop;
    importclass("geografico");
    $html->asignar("version", "patios");
    $patio = new Patio_Evento($subop);
    if (!$patio->existe) {
        $html->asignar("msg", "No existe el patio indicado");
        $html->plantilla("error.tpl");
        $html->ver();
        exit;
    }
    $ejes = new Programa_Categoria_Controladora();
    $ejes = $ejes->getNivel(0);

    //MOSTRAR PLANTILLA
    if (isset($_SERVER['HTTP_REFERER'])) {
        $url_anterior = $_SERVER['HTTP_REFERER'];
    } else {
        $url_anterior = "/patios/listado/";
    }
    $html->asignar("url_anterior", $url_anterior);

    $html->asignar("patio", $patio);
    $html->asignar("ejes", $ejes);
    $html->asignar("ip", getIPv4());
    $html->titulo('Patio ' . $patio->ciudad . " - Inscríbete");
    $html->descripcion('En el Patio de ' . $patio->ciudad . ' discutiremos las enmiendas recibidas y daremos forma al programa de Adelante Andalucía');
    $html->plantilla("patio_inscripcion.tpl");
    $html->ver();
}

function patio_inscripcion_enviar() {
    global $db;

    //var_dump($_POST);
    $id = new Patio_Inscripcion($_POST);
    $id->ejes = new Programa_Categoria($id->ejes);

    if ($id) {

        $email = new Correo();
        $html = "";

        $html .= "<strong>ID: </strong>" . $id->id . "<br>";
        $html .= "<strong>PATIO: </strong>" . $id->patio->ciudad . "<br>";
        $html .= "<strong>NOMBRE: </strong>" . $id->nombre . "<br>";
        $html .= "<strong>APELLIDOS: </strong>" . $id->apellidos . "<br>";
        $html .= "<strong>CP: </strong>" . $id->cp->cp . " " . $id->cp->municipio . "<br>";
        $html .= "<strong>EMAIL: </strong>" . $id->email . "<br>";
        $html .= "<strong>TELEFONO: </strong>" . $id->telefono . "<br>";
        $html .= "<strong>LUDOTECA: </strong>" . (($id->ludoteca) ? "Sí" : "No") . "<br>";
        $html .= "<strong>ANDALUZ: </strong>" . (($id->andaluz) ? "Sí" : "No") . "<br>";
        $html .= "<strong>BUS: </strong>" . (($id->bus) ? "Sí" : "No") . "<br>";
        $html .= "<strong>OBSERVACIONES: </strong>" . $id->observaciones . "<br>";
        $html .= "<strong>EJE: </strong>" . $id->ejes->id . " - " . $id->ejes->nombre . "<br>";


        //echo $html;
        $email->fromtxt = $id->nombre . " " . $id->apellidos;
        $email->asunto = "INSCRIPCION PATIO: " . $id->patio->ciudad;
        $email->from = MAIL_ADMIN;
        $email->to = MAIL_ENMIENDAS;
        $email->enviar($html);
        //var_dump($id);        
    }
}

function enmiendas() {
    global $db, $html, $op, $subop;

    $html->asignar("version", 'patios');

    $categorias = new Programa_Categoria_Controladora();
    $categorias = $categorias->getCategorias(true, false);

    $provincia = cp2prov($op);

    $html->asignar("provincia", $provincia);

    $out = array();
    foreach ($categorias as $categoria) {

        //Rescatamos enmiendas a la categoría        
        $enmiendasAlEpigrafe = new Programa_Enmienda_Controladora();
        $enmiendasAlEpigrafe->estado = 1;
        $tmp = $enmiendasAlEpigrafe->getEnmiendasFrom($categoria->id, 'idCategoria', $op);
        $categoria->enmiendas = $tmp;

        //Rescatamos las propuestas
        $propuestas = new Programa_Propuesta_Controladora();
        $tmp = $propuestas->getPropuestasCategoria($categoria->id);
        $categoria->propuestas = $tmp;
        foreach ($categoria->propuestas as $p) {
            $enmiendas = new Programa_Enmienda_Controladora();
            $enmiendas->estado = 1;
            $tmp = $enmiendasAlEpigrafe->getEnmiendasFrom($p->id, 'idPropuesta', $op);
            $p->enmiendas = $tmp;
        }

        //Recatamos las intros
        $intros = new Programa_Propuesta_Controladora();
        $tmp = $propuestas->getPropuestasCategoria($categoria->id, 'intro');
        $categoria->intro = $tmp;
        foreach ($categoria->intro as $p) {
            $enmiendasAlIntro = new Programa_Enmienda_Controladora();
            $enmiendasAlIntro->estado = 1;
            $tmp = $enmiendasAlIntro->getEnmiendasFrom($p->id, 'idPropuesta', $op);
            $p->enmiendas = $tmp;
        }
        $out[] = $categoria;
    }

    //var_dump($out);

    $html->asignar("ip", getIPv4());
    $html->asignar("programa", $out);
    $html->plantilla("enmiendas_pdf.tpl");
    $html->ver();
}

function enmiendas_andaluz() {
    global $db, $html, $op, $subop;

    $html->asignar("version", 'patios');

    $categorias = new Programa_Categoria_Controladora();
    $categorias = $categorias->getCategorias(true, false);

    if ($op == 'A,B') {
        $html->asignar("texto_libre", 'PATIO ANDALUZ<br>PROPUESTAS CONSENSUADAS');
    } elseif ($op == 'C,D') {
        $html->asignar("texto_libre", 'PATIO ANDALUZ<br>PROPUESTAS A DEBATE');
    } elseif ($op == 'A') {
        $html->asignar("texto_libre", 'PATIO ANDALUZ<br>PROPUESTAS CONSENSUADAS');
    } elseif ($op == 'B') {
        $html->asignar("texto_libre", 'PATIO ANDALUZ<br>PROPUESTAS CONSENSUADAS');
    } elseif ($op == 'C') {
        $html->asignar("texto_libre", 'PATIO ANDALUZ<br>PROPUESTAS A DEBATE');
    } elseif ($op == 'D') {
        $html->asignar("texto_libre", 'PATIO ANDALUZ<br>PROPUESTAS A DEBATE');
    } else {
        $html->asignar("texto_libre", 'PATIO ANDALUZ 6 OCTUBRE 2018');
    }


    //$op = "A";

    $modoTransaccion = false;
    if ($op == 'B') {
        $modoTransaccion = true;
    }

    $out = array();
    foreach ($categorias as $categoria) {

        //Rescatamos enmiendas a la categoría        
        $enmiendasAlEpigrafe = new Programa_Enmienda_Controladora();
        $enmiendasAlEpigrafe->estado = 1;
        $enmiendasAlEpigrafe->andaluz = $op;
        $enmiendasAlEpigrafe->transaccion = $modoTransaccion;
        $tmp = $enmiendasAlEpigrafe->getEnmiendasFrom($categoria->id, 'idCategoria');
        $categoria->enmiendas = $tmp;

        //Rescatamos las propuestas
        $propuestas = new Programa_Propuesta_Controladora();
        $tmp = $propuestas->getPropuestasCategoria($categoria->id);
        $categoria->propuestas = $tmp;
        foreach ($categoria->propuestas as $p) {
            $enmiendas = new Programa_Enmienda_Controladora();
            $enmiendas->estado = 1;
            $enmiendas->andaluz = $op;
            $enmiendas->transaccion = $modoTransaccion;
            $tmp = $enmiendasAlEpigrafe->getEnmiendasFrom($p->id, 'idPropuesta');
            $p->enmiendas = $tmp;
        }

        //Recatamos las intros
        $intros = new Programa_Propuesta_Controladora();
        $tmp = $propuestas->getPropuestasCategoria($categoria->id, 'intro');
        $categoria->intro = $tmp;
        foreach ($categoria->intro as $p) {
            $enmiendasAlIntro = new Programa_Enmienda_Controladora();
            $enmiendasAlIntro->estado = 1;
            $enmiendasAlIntro->andaluz = $op;
            $enmiendasAlIntro->transaccion = $modoTransaccion;
            $tmp = $enmiendasAlIntro->getEnmiendasFrom($p->id, 'idPropuesta');
            $p->enmiendas = $tmp;
        }
        $out[] = $categoria;
    }

    //var_dump($out);

    $html->asignar("ip", getIPv4());
    $html->asignar("programa", $out);
    $html->plantilla("enmiendas_pdf.tpl");
    $html->ver();
}

function consultas() {
    global $html;
    $html->asignar("tipo", 'enmienda');
    $html->plantilla("blanco.tpl");
    $html->ver(CONF_HOME . "tpl/busqueda.tpl");
}

function entrar() {
    global $op;

    if ($op == sha1(ADMIN_PASS)) {
        $_SESSION['ponencia'] = 1;
    } else {
        $_SESSION['ponencia'] = 0;
        unset($_SESSION['ponencia']);
        session_destroy();
    }
    header("Location: /consultas/");
}

function salir() {
    unset($_SESSION['ponencia']);
    session_destroy();
    header("Location: /consultas/");
}

function consultas_enviar() {
    global $html, $op, $subop;

    $tipo = $_POST['tipo'];
    $id = $_POST['id'];


    if ($tipo == 'enmienda') {
        call_user_func('enmienda', false, $id);
    } elseif ($tipo == 'propuesta') {
        call_user_func('propuesta', $id, 'Propuesta', false);
    } elseif ($tipo == 'parrafo') {
        if (!(substr($id, 0, 1) === 'P')) {
            $id = 'P' . $id;
        }
        call_user_func('propuesta', $id, 'Parrafo', false);
    } else {
        $html->plantilla("blanco.tpl");
    }

    $html->asignar("tipo", $_POST['tipo']);
    $html->ver(CONF_HOME . "tpl/busqueda.tpl");
}

function valoracion_patio() {
    global $subop, $html, $c;
    $html->asignar("version", 'patios');

    if (isset($_GET['orden'])) {
        $orden = $_GET['orden'];
    } else {
        $orden = 'PDF';
    }

    $arrayEnmiendas = new Programa_Enmienda_Controladora();
    $arrayEnmiendas->estado = 1;
    $arrayEnmiendas->valoraciones = true;
    $arrayEnmiendas->soloPonencia = true;
    if ($orden == 'PDF') {
        $arrayEnmiendas->ordenPDF = true;
    } else {
        $arrayEnmiendas->ordenPDF = false;
    }
    $enmiendas = $arrayEnmiendas->getEnmiendasProvincia($subop);

    $arrayEnmiendas2 = new Programa_Enmienda_Controladora();
    $arrayEnmiendas2->estado = 0;
    $arrayEnmiendas2->valoraciones = true;
    $arrayEnmiendas2->soloPonencia = true;
    if ($orden == 'PDF') {
        $arrayEnmiendas2->ordenPDF = true;
    } else {
        $arrayEnmiendas2->ordenPDF = false;
    }
    $enmiendas_denegadas = $arrayEnmiendas2->getEnmiendasProvincia($subop);

    $html->asignar("orden", $orden);
    $html->asignar("provincia", $subop);
    $html->asignar("enmiendas", $enmiendas);
    $html->asignar("colectivos", null);
    $html->asignar("enmiendas_denegadas", $enmiendas_denegadas);
    $html->plantilla("valoraciones.tpl");
    $html->ver();
}

function valoracion_andaluz() {
    global $subop, $html, $c, $op;
    $html->asignar("version", 'patios');

    if (isset($_GET['orden'])) {
        $orden = $_GET['orden'];
    } else {
        $orden = 'PDF';
    }

    $arrayEnmiendas = new Programa_Enmienda_Controladora();
    $arrayEnmiendas->estado = 1;
    $arrayEnmiendas->valoraciones = true;
    $arrayEnmiendas->soloPonencia = true;
    $arrayEnmiendas->transaccion = false;
    $arrayEnmiendas->andaluz = $op;

    if ($orden == 'PDF') {
        $arrayEnmiendas->ordenPDF = true;
    } else {
        $arrayEnmiendas->ordenPDF = false;
    }

    $enmiendas = $arrayEnmiendas->getEnmiendas();

    $html->asignar("orden", $orden);
    $html->asignar("provincia", $subop);
    $html->asignar("enmiendas", $enmiendas);
    $html->asignar("enmiendas_denegadas", null);
    $html->plantilla("valoraciones.tpl");
    $html->ver();
}

function valoracion_patio_guardar() {
    global $subop, $html;
    $html->asignar("version", 'patios');

    if (!$_POST) {
        exit;
    }

    $sentido = $_POST['sentido'];
    $observaciones = $_POST['observaciones'];
    $enmiendas = $_POST['enmienda'];
    $contador = 0;
    //var_dump($_POST);
    foreach ($_POST['enmienda'] as $clave => $valor) {
        //var_dump($sentido[$clave]);
        if ($sentido[$clave] != '0') {
            unset($id);
            $id['enmiendaID'] = $enmiendas[$clave];
            $id['valorador'] = $_POST['provincia'];
            $id['valoracion'] = $sentido[$clave];
            $id['observaciones'] = $observaciones[$clave];
            $valoracion = new Programa_Enmienda_Valoracion($id);
            $contador++;
            //var_dump($id);
        }
        //echo $clave . " - " . $valor . "<br>";
    }
    $html->asignar("colectivos", null);
    $html->asignar("msg", "Se han almacenado correctamente las resoluciones enviadas (" . $contador . ").");
    $html->plantilla('error.tpl'); //NO ES UN ERROR. ES UN MENSAJE SIMPLE
    $html->ver();
}

function cp2prov($cp) {
    switch ($cp) {
        case '04': $provincia = "Almeria";
            break;
        case '11': $provincia = "Cádiz";
            break;
        case '14': $provincia = "Córdoba";
            break;
        case '18': $provincia = "Granada";
            break;
        case '21': $provincia = "Huelva";
            break;
        case '23': $provincia = "Jaén";
            break;
        case '29': $provincia = "Málaga";
            break;
        case '41': $provincia = "Sevilla";
            break;
        default: exit;
    }
    return $provincia;
}
