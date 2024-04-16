import InputRadio from "./InputRadio";

const InputInstallments = ({hasRecurrence, totalAmount, totalInstallments, setHelperVisibility}) => {
    if (hasRecurrence) {
        totalInstallments = 1;
    }

    const createInstallment = (radioName, radioValue, radioId, amount, installment) => {
        const formattedAmount = amount.toLocaleString('pt-br', {style: 'currency', currency: 'BRL'});

        const labelOnClickHandler = (event) => {
            const installmentRadio = document.getElementById(radioId);
            installmentRadio.checked = true;

            const installmentHidden = document.getElementById('card-selected-installment-hidden')
            if (installmentHidden) {
                installmentHidden.value = installmentRadio.value
            }
            setHelperVisibility(false);
        }

        const installmentDisplay = installment > (totalInstallments / 2) && totalInstallments >= 6 ? 'none' : 'flex';

        return (
            <div className={'mp-input-table-item'} style={{display: installmentDisplay}}>
                <div className={'mp-input-table-label'} onClick={labelOnClickHandler}>
                    <div className={'mp-input-table-option'}>
                        <InputRadio name={radioName} value={radioValue} radioId={radioId}/>

                        <span className={'mp-input-table-row-text'}>
                            {installment === 1 ? `${formattedAmount} à Vista` : `${installment} Parcelas de ${formattedAmount}`}
                        </span>
                    </div>

                    <span className={'mp-input-table-row-obs-highlight'}>Sem acréscimos</span>
                </div>
            </div>
        )
    };

    const createInstallmentsList = (totalAmount, totalInstallments) => {
        let installmentsComponent = [];

        for (let installment = 1; installment <= totalInstallments; installment++) {
            const installmentAmount = totalAmount / installment;
            const radioId = 'installment-' + installment

            const component = createInstallment('card-installments', installment, radioId, installmentAmount, installment);

            installmentsComponent.push(component);
        }

        return installmentsComponent;
    }

    const moreOptionsOnClickHandler = (event) => {
        const installments = document.getElementsByClassName('mp-input-table-item');
        for (const installment of installments) {
            installment.style.display = 'flex';
        }
    }

    const moreActionsDisplay = totalInstallments >= 6 ? 'flex' : 'none';


    return (
        <div id={'mp-checkout-custom-installments-container'} className={'mp-checkout-custom-installments-container'}>
            <div className={'mp-input-table-container'} data-cy={'input-table-container'}>
                <div className={'mp-input-table-list'} data-cy={'input-table-list'}>

                    {createInstallmentsList(totalAmount, totalInstallments)}

                    <div className={'mp-input-table-container-link'} onClick={moreOptionsOnClickHandler}
                         style={{display: moreActionsDisplay}}>
                        <a id={'more-options'} className={'mp-input-table-link'}>Mais opções</a>
                    </div>
                </div>
                <input type={'hidden'}
                       name={'card-selected-installment-hidden'}
                       id={'card-selected-installment-hidden'}
                />

            </div>
        </div>
    )
};

export default InputInstallments;