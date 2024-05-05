<?php

namespace Majordome\Twig;

use Majordome\Entity\Run;
use Majordome\Resource\Resource;
use Majordome\Resource\Url\AWSResourceUrlGenerator;
use Majordome\Rule\Provider;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class MajordomeTwigExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('generate_resource_url', function (Run $run, Resource $resource) {
                if (Provider::tryFrom($run->getProvider()) == Provider::AWS) {
                    $awsResourceUrlGenerator = new AWSResourceUrlGenerator($run->getRegion());
                    return $awsResourceUrlGenerator->generateUrl($resource);
                } else {
                    return $resource->getId();
                }
            }),
        ];
    }
}
