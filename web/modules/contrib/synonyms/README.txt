
-- SUMMARY --

The Synonyms module enriches content Entities with the notion of synonyms.
Currently the module provides the following functionality:
* support of synonyms through Fields both base and attached ones. Any field,
  for which synonyms provider exists, can be enabled as source of synonyms.
* synonyms-friendly autocomplete and select widgets for entity reference field
  type.
* integration with Views: Synonyms module provides a few filters and contextual
  filters that allow filtering not only by entity name but also by one of its
  synonyms.
* 2 form elements are provided for developers: synonyms-friendly entity
  autocomplete and select.

-- REQUIREMENTS --

The Synonyms module requires only Drupal core.

-- SUPPORTED SYNONYMS PROVIDERS --

Module ships with ability to provide synonyms from the following field types:
* "Text" field type
* "Entity Reference" field type
* "Number" field type
* "Float" field type
* "Decimal" field type
* "Email" field type
* "Telephone" field type

Worth mentioning here: this list is easily extended further by implementing new
synonyms providers in your code. Refer to Synonyms documentation for more
details on how to accomplish it.

-- GRANULATION WITHIN SYNONYMS BEHAVIOR --

In order to achieve greater flexibility, this module introduced additional
granularity into what "synonyms" mean. This granularity is expressed via
"synonyms behavior" idea whatsoever. Therefore each synonyms behavior may have
its own synonyms provider than you can enable and configure through admin UI of
the Synonyms module. For example, field "Typos" can be part of autocomplete
behavior, while field "Other spellings" can be part of select behavior.
Currently the following synonym behaviors are recognized (other modules actually
can extend this list):
* Autocomplete - whether synonyms from this provider should participate in
  autocomplete suggestions. This module ships with synonyms friendly
  autocomplete widget and the autocomplete suggestions will be filled in with
  the synonyms from enabled providers for "autocomplete" behavior
* Select - whether synonyms from this provider should be included in the
  synonyms friendly select widget.

That way on the Synonyms configuration page you will get to add/remove certain
synonym providers for certain synonym behaviors. Also, each behavior and
provider may have its own settings that you get to configure too.

-- INSTALLATION --

* Install as usual

-- CONFIGURATION --

* You can configure synonyms of all eligible entity types by going to Admin ->
  Structure -> Synonyms (/admin/structure/synonyms)
