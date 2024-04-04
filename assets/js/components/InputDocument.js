import InputLabel from './InputLabel';
import InputHelper from './InputHelper';
import {useState} from '@wordpress/element';
import CPFValidator from "../validators/documents/CPFValidator";
import CNPJValidator from "../validators/documents/CNPJValidator";
import CIValidator from "../validators/documents/CIValidator";

const InputDocument = ({
                           labelMessage,
                           inputName,
                           hiddenId,
                           inputDataCheckout,
                           selectId,
                           selectName,
                           selectDataCheckout,
                           flagError,
                           documents,
                           validate,
                           helperVisibility,
                           setHelperVisibility
                       }) => {

    const [documentInputMaxLengthState, setDocumentInputMaxLengthState] = useState(14);
    const [documentInputPlaceholderState, setDocumentInputPlaceholderState] = useState('999.999.999-99');
    const [documentInputNameState, setDocumentInputNameState] = useState(inputName);

    const selectOnFocusHandler = (event) => {
        if (validate) {
            const component = document.getElementById('form-checkout__identificationNumber-container');
            component.classList.add('mp-focus');
            component.classList.remove('mp-error');

            setHelperVisibility(false);
        }
    };

    const selectOnBlurHandler = (event) => {
        if (validate) {
            const component = document.getElementById('form-checkout__identificationNumber-container');
            component.classList.remove('mp-focus');

            setHelperVisibility(false);
        }
    };

    const setInputDocumentMask = (event) => {
        const select = document.getElementById(selectId);
        const hiddenInput = document.getElementById(hiddenId);

        const masks = {
            CPF: (value) =>
                value
                    .replace(/\D+/g, '')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d{1,2})/, '$1-$2')
                    .replace(/(-\d{2})\d+?$/, '$1'),
            CNPJ: (value) =>
                value
                    .replace(/\D+/g, '')
                    .replace(/(\d{2})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1.$2')
                    .replace(/(\d{3})(\d)/, '$1/$2')
                    .replace(/(\d{4})(\d)/, '$1-$2')
                    .replace(/(-\d{2})\d+?$/, '$1'),
            CI: (value) => value.replace(/\D+/g, ''),
        };

        if (typeof masks[select.value] !== 'undefined') {
            event.target.value = masks[select.value](event.target.value);
        }

        if (hiddenInput) {
            hiddenInput.value = event.target.value.replace(/[^\w\s]/gi, '');
        }

    };

    const selectOnInputHandler = (event) => {
        const select = document.getElementById(selectId);
        const documentInput = document.getElementById('input-document');

        documentInput.value = '';

        if (select.value === 'CPF') {
            setDocumentInputMaxLengthState(14);
            setDocumentInputPlaceholderState('999.999.999-99');
        } else if (select.value === 'CNPJ') {
            setDocumentInputMaxLengthState(18);
            setDocumentInputPlaceholderState('99.999.999/0001-99');
        } else if (select.value === 'CI') {
            setDocumentInputMaxLengthState(8);
            setDocumentInputPlaceholderState('99999999');
        } else {
            setDocumentInputMaxLengthState(20);
            setDocumentInputPlaceholderState('');
        }
    };

    const inputDocumentOnBlurHandler = (event) => {
        const validatorSelector = {
            CPF: CPFValidator,
            CNPJ: CNPJValidator,
            CI: CIValidator
        }

        const select = document.getElementById(selectId);
        if (typeof validatorSelector[select.value] !== 'undefined') {
            let validator = validatorSelector[select.value];

            let isDocumentValid = validator(event.target.value);

            const mpInput = document.getElementById('form-checkout__identificationNumber-container');

            if (isDocumentValid) {
                mpInput.classList.remove('mp-error');
                mpInput.classList.remove('mp-focus');

                setHelperVisibility(false);
                setDocumentInputNameState(inputName);
            } else {
                mpInput.classList.add('mp-error');
                setHelperVisibility(true);
                setDocumentInputNameState(flagError);
            }

        }


    };

    const documentOptions = documents.map(document =>
        <option value={document}>{document}</option>
    );

    return (
        <div className={'mp-input-document'} data-cy={'input-document-container'}>
            <InputLabel isOptional={false} message={labelMessage}/>
            <div className={'mp-input'} id={'form-checkout__identificationNumber-container'}>
                <select className={'mp-document-select'}
                        name={selectName}
                        id={selectId}
                        data-checkout={selectDataCheckout}
                        data-cy={'select-document'}
                        onFocus={selectOnFocusHandler}
                        onBlur={selectOnBlurHandler}
                        onInput={selectOnInputHandler}>
                    {documentOptions}
                </select>

                <div className={'mp-vertical-line'}></div>
                <input name={documentInputNameState}
                       data-checkout={inputDataCheckout}
                       data-cy={'input-document'}
                       id={'input-document'}
                       className={'mp-document'}
                       type={'text'}
                       inputMode={'text'}
                       maxLength={documentInputMaxLengthState}
                       placeholder={documentInputPlaceholderState}
                       onFocus={selectOnFocusHandler}
                       onBlur={inputDocumentOnBlurHandler}
                       onInput={setInputDocumentMask}
                />
            </div>
            <input type={'hidden'} id={hiddenId}/>
            <InputHelper isVisible={helperVisibility} message={'Número de documento inválido'} inputId={'mp-doc-number-helper'}/>
        </div>
    );
}

export default InputDocument;
