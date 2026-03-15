..  include:: /Includes.rst.txt

=================
Extending the API
=================

The extension is designed for extensibility from day one. Third-party
extensions can register custom normalizers, override query services, and
add new endpoints.

Custom content element normalizer
==================================

Implement ``ContentElementNormalizerInterface`` and tag it in your
``Configuration/Services.yaml``:

..  code-block:: php

    namespace Vendor\MyExt\Normalizer\ContentElement;

    use DMF\ContentApi\Dto\ContentElementDto;
    use DMF\ContentApi\Normalizer\ContentElementNormalizerInterface;

    final class MyCustomNormalizer implements ContentElementNormalizerInterface
    {
        public function supportsCType(): string
        {
            return 'my_custom_ctype';
        }

        public function normalize(array $row): ContentElementDto
        {
            return new ContentElementDto(
                uid: $row['uid'],
                type: $this->supportsCType(),
                colPos: $row['colPos'],
                data: ['title' => $row['header']],
            );
        }
    }

..  code-block:: yaml

    services:
      Vendor\MyExt\Normalizer\ContentElement\MyCustomNormalizer:
        tags: ['content_api.content_element_normalizer']

Custom query service
====================

Override an existing query service by aliasing its interface in your
extension's ``Configuration/Services.yaml``:

..  code-block:: yaml

    services:
      DMF\ContentApi\Query\PageQueryServiceInterface:
        alias: Vendor\MyExt\Query\CustomPageQueryService
