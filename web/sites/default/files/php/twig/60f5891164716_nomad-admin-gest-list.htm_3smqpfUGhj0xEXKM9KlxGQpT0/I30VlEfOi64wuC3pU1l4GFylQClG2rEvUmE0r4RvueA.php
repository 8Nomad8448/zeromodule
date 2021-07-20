<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* modules/custom/nomad/templates/nomad-admin-gest-list.html.twig */
class __TwigTemplate_a7967d27dd9a77b9dbec11b371bd83d8c1232c499764f8ef40cdf5c5a358e2ea extends \Twig\Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
        $this->sandbox = $this->env->getExtension('\Twig\Extension\SandboxExtension');
        $this->checkSecurity();
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("nomad/gests-style"), "html", null, true);
        echo "
";
        // line 2
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->extensions['Drupal\Core\Template\TwigExtension']->attachLibrary("core/drupal.dialog.ajax"), "html", null, true);
        echo "

<div class=\"row\">
  <div class=\"col-12\">
    ";
        // line 6
        echo $this->extensions['Drupal\Core\Template\TwigExtension']->escapeFilter($this->env, $this->sandbox->ensureToStringAllowed(($context["form"] ?? null), 6, $this->source), "html", null, true);
        echo "
  </div>
</div>
<button id=\"myBtn\" class=\"button\">Delete selected</button>
";
    }

    public function getTemplateName()
    {
        return "modules/custom/nomad/templates/nomad-admin-gest-list.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  50 => 6,  43 => 2,  39 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("{{ attach_library('nomad/gests-style') }}
{{ attach_library('core/drupal.dialog.ajax') }}

<div class=\"row\">
  <div class=\"col-12\">
    {{ form }}
  </div>
</div>
<button id=\"myBtn\" class=\"button\">Delete selected</button>
", "modules/custom/nomad/templates/nomad-admin-gest-list.html.twig", "/var/www/web/modules/custom/nomad/templates/nomad-admin-gest-list.html.twig");
    }
    
    public function checkSecurity()
    {
        static $tags = array();
        static $filters = array("escape" => 1);
        static $functions = array("attach_library" => 1);

        try {
            $this->sandbox->checkSecurity(
                [],
                ['escape'],
                ['attach_library']
            );
        } catch (SecurityError $e) {
            $e->setSourceContext($this->source);

            if ($e instanceof SecurityNotAllowedTagError && isset($tags[$e->getTagName()])) {
                $e->setTemplateLine($tags[$e->getTagName()]);
            } elseif ($e instanceof SecurityNotAllowedFilterError && isset($filters[$e->getFilterName()])) {
                $e->setTemplateLine($filters[$e->getFilterName()]);
            } elseif ($e instanceof SecurityNotAllowedFunctionError && isset($functions[$e->getFunctionName()])) {
                $e->setTemplateLine($functions[$e->getFunctionName()]);
            }

            throw $e;
        }

    }
}
