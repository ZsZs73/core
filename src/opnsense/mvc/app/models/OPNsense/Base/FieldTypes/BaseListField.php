<?php

/**
 *    Copyright (C) 2019 Deciso B.V.
 *
 *    All rights reserved.
 *
 *    Redistribution and use in source and binary forms, with or without
 *    modification, are permitted provided that the following conditions are met:
 *
 *    1. Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 *
 *    2. Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *
 *    THIS SOFTWARE IS PROVIDED ``AS IS'' AND ANY EXPRESS OR IMPLIED WARRANTIES,
 *    INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY
 *    AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 *    AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY,
 *    OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 *    SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 *    INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 *    CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 *    ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 *    POSSIBILITY OF SUCH DAMAGE.
 *
 */
namespace OPNsense\Base\FieldTypes;

use Phalcon\Validation\Validator\InclusionIn;
use OPNsense\Base\Validators\CsvListValidator;

/**
 * Class BaseListField
 * @package OPNsense\Base\FieldTypes
 */
abstract class BaseListField extends BaseField
{
    /**
     * @var bool marks if this is a data node or a container
     */
    protected $internalIsContainer = false;

    /**
     * @var array valid options for this list
     */
    protected $internalOptionList = array();

    /**
     * @var string default description for empty item
     */
    private $internalEmptyDescription = null;

    /**
     * @var bool field may contain multiple interfaces at once
     */
    private $internalMultiSelect = false;

    /**
     * @var string default validation message string
     */
    protected $internalValidationMessage = "option not in list";

    /**
     * select if multiple interfaces may be selected at once
     * @param $value boolean value 0/1
     */
    public function setMultiple($value)
    {
        if (trim(strtoupper($value)) == "Y") {
            $this->internalMultiSelect = true;
        } else {
            $this->internalMultiSelect = false;
        }
    }

    /**
     * set descriptive text for empty value
     * @param $value string description
     */
    public function setBlankDesc($value)
    {
        $this->internalEmptyDescription = gettext($value);
    }

    /**
     * get valid options, descriptions and selected value
     * @return array
     */
    public function getNodeData()
    {
        if (empty($this->internalEmptyDescription)) {
            $this->internalEmptyDescription = gettext("none");
        }
        $result = array();
        // if option is not required, add empty placeholder
        if (!$this->internalIsRequired && !$this->internalMultiSelect) {
            $result[""] = array("value"=>$this->internalEmptyDescription, "selected" => 0);
        }

        // explode options
        $options = explode(',', $this->internalValue);
        foreach ($this->internalOptionList as $optKey => $optValue) {
            if (in_array($optKey, $options)) {
                $selected = 1;
            } else {
                $selected = 0;
            }
            $result[$optKey] = array("value"=>$optValue, "selected" => $selected);
        }

        return $result;
    }


    /**
     * @inheritdoc
     */
    public function getValidators()
    {
        $validators = parent::getValidators();
        if ($this->internalValue != null) {
            if ($this->internalMultiSelect) {
                // field may contain more than one option
                $validators[] = new CsvListValidator(array('message' => $this->internalValidationMessage,
                    'domain'=>array_keys($this->internalOptionList)));
            } else {
                // single option selection
                $validators[] = new InclusionIn(array('message' => $this->internalValidationMessage,
                    'domain'=>array_keys($this->internalOptionList)));
            }
        }
        return $validators;
    }
}
