<?php

namespace BackSystem\Base\Helper\Paginator;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

final class PageOutOfBoundException extends BadRequestHttpException
{
}
