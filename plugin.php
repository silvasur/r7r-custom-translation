<?php

class custom_translation extends RatatoeskrPlugin
{
    public $current_language;
    public $current_translation;

    public function ste_tag_cunstom_translation($ste, $params, $sub)
    {
        $this->load_language($ste->vars["language"]);
        if($ste->evalbool($params["raw"]))
            return (string) $this->current_translation[$params["for"]];
        else
            return htmlesc((string) $this->current_translation[$params["for"]]);
    }

    public function pluginpage(&$data, $url_now, &$url_next)
    {
        global $languages;
        $this->prepare_backend_pluginpage();

        $tr_langs         = $this->kvstorage["languages"];
        $translation_keys = $this->kvstorage["translation_keys"];

        $translations = array();
        foreach($tr_langs as $l)
            $translations[$l] = $this->kvstorage["translation_$l"];

        if(isset($_POST["tr_translation_enter"]) and (!empty($_POST["tr_translation_key"])) and (!empty($_POST["tr_lang"])))
        {
            if(!isset($translations[$_POST["tr_lang"]]))
            {
                $tr_langs[] = $_POST["tr_lang"];
                $translation = array();
            }
            else
                $translation = $translations[$_POST["tr_lang"]];

            if(empty($_POST["tr_translation"]))
            {
                if(in_array($_POST["tr_translation_key"], $translation_keys))
                {
                    unset($translation[$_POST["tr_translation_key"]]);
                    if(empty($translation))
                    {
                        $tr_langs = array_filter($tr_langs, function($l) { return $l != $_POST["tr_lang"]; });
                        $this->kvstorage["languages"] = $tr_langs;
                    }
                }
            }
            else
            {
                $translation[$_POST["tr_translation_key"]] = $_POST["tr_translation"];
                if(!in_array($_POST["tr_translation_key"], $translation_keys))
                    $translation_keys[] = $_POST["tr_translation_key"];
            }

            $translations[$_POST["tr_lang"]] = $translation;
            $this->kvstorage["translation_" . $_POST["tr_lang"]] = $translation;
            $this->kvstorage["translation_keys"] = $translation_keys;
        }

        sort($tr_langs);

        $this->kvstorage["languages"] = $tr_langs;

        $this->ste->vars["tr_langs"] = array_map(function($l) use ($languages) { return $languages[$l]["language"]; }, $tr_langs);
        $this->ste->vars["tr_translations"] = array();
        foreach($translation_keys as $tk)
        {
            $this->ste->vars["tr_translations"][$tk] = array();
            $nothinghere = True;
            foreach($tr_langs as $lang)
            {
                $translation = (string) @$translations[$lang][$tk];
                $this->ste->vars["tr_translations"][$tk][] = $translation;
                if(!empty($translation))
                    $nothinghere = False;
            }
            if($nothinghere)
            {
                unset($this->ste->vars["tr_translations"][$tk]);
                $translation_keys = array_filter($translation_keys, function($k) use ($tk) { return $tk != $k; });
                $this->kvstorage["translation_keys"] = $translation_keys;
            }
        }

        echo $this->ste->exectemplate($this->get_template_dir() . "/backend.html");
    }

    private function load_language($langcode)
    {
        if($langcode == $this->current_language)
            return;

        $this->current_translation = (array) $this->kvstorage["translation_$langcode"];
        $this->current_language = $langcode;
    }

    public function install()
    {
        $this->kvstorage["languages"] = array();
        $this->kvstorage["translation_keys"] = array();
    }

    public function init()
    {
        $this->ste->register_tag("custom_translation", array($this, "ste_tag_cunstom_translation"));
        $this->register_backend_pluginpage("Custom Translations", array($this, "pluginpage"));
    }
}
