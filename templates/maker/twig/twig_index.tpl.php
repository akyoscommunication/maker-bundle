{% extends '<?= $extend_template; ?>' %}

{% block title %}{% endblock %}

{% block <?= $block ?> %}
    <a href="{{ path('<?= $default_route; ?>.new') }}">New</a>
    <twig:<?= $componentName; ?>:Index />
{% endblock %}
