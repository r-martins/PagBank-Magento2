<?php
declare(strict_types=1);

namespace RicardoMartins\PagBank\Block\Adminhtml\System;

use RicardoMartins\PagBank\Model\System\Config\VindiAdminNotes;

class BoletoInstructionComment extends AbstractPartnerComment
{
    public function getCommentText($elementValue): string
    {
        if (!$this->isVindi()) {
            return '';
        }

        return VindiAdminNotes::boletoInstructionsNote();
    }
}
