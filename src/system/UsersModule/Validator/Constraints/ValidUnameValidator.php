<?php

namespace Zikula\UsersModule\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zikula\Common\Translator\TranslatorInterface;
use Zikula\ExtensionsModule\Api\VariableApi;
use Zikula\UsersModule\Constant as UsersConstant;
use Zikula\UsersModule\Entity\Repository\UserRepository;

class ValidUnameValidator extends ConstraintValidator
{
    /**
     * @var VariableApi
     */
    private $variableApi;
    /**
     * @var TranslatorInterface
     */
    private $translator;
    /**
     * @var UserRepository
     * @todo refactor to use Interface when appropriate
     */
    private $userRepository;
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * ValidUnameValidator constructor.
     * @param VariableApi $variableApi
     * @param TranslatorInterface $translator
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(VariableApi $variableApi, TranslatorInterface $translator, UserRepository $userRepository, ValidatorInterface $validator)
    {
        $this->variableApi = $variableApi;
        $this->translator = $translator;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    public function validate($value, Constraint $constraint)
    {
        /** @var ConstraintViolationListInterface $errors */
        $errors = $this->validator->validate($value, [
            new NotBlank(),
            new Type('string'),
            new Length([
                'min' => 1,
                'max' => UsersConstant::UNAME_VALIDATION_MAX_LENGTH
            ]),
            new Regex([
                'pattern' => '/^' . UsersConstant::UNAME_VALIDATION_PATTERN . '$/uD',
                'message' => $this->translator->__('The value does not appear to be a valid user name. A valid user name consists of lowercase letters, numbers, underscores, periods or dashes.')
            ])
        ]);
        if (count($errors) > 0) {
            foreach ($errors as $error) {
                // this method forces the error to appear at the form input location instead of at the top of the form
                $this->context->buildViolation($error->getMessage())->addViolation();
            }
        }

        // ensure all lowercase
        if ($value != mb_strtolower($value)) {
            $this->context->buildViolation($this->translator->__('The username cannot use uppercase letters'))
                ->setParameter('%string%', $value)
                ->addViolation();
        }

        // ensure not reserved/illegal
        // @todo old method allowed Admin to skip this check
        $illegalUserNames = $this->variableApi->get('ZikulaUsersModule', UsersConstant::MODVAR_REGISTRATION_ILLEGAL_UNAMES, '');
        if (!empty($illegalUserNames)) {
            $pattern = array('/^(\s*,\s*|\s+)+/D', '/\b(\s*,\s*|\s+)+\b/D', '/(\s*,\s*|\s+)+$/D');
            $replace = array('', '|', '');
            $illegalUserNames = preg_replace($pattern, $replace, preg_quote($illegalUserNames, '/'));
            if (preg_match("/^({$illegalUserNames})/iD", $value)) {
                $this->context->buildViolation($this->translator->__('The user name you entered is reserved. It cannot be used.'))
                    ->setParameter('%string%', $value)
                    ->addViolation();
            }
        }

        // ensure unique
        $qb = $this->userRepository->createQueryBuilder('u')
            ->select('count(u.uid)')
            ->where('u.uname = :uname')
            ->setParameter('uname', $value);
        // when updating an existing User, the existing Uid must be excluded.
        if (!empty($constraint->excludedUid)) {
            $qb->andWhere('u.uid <> :excludedUid')
                ->setParameter('excludeUid', $constraint->excludedUid);
        }

        if ((int)$qb->getQuery()->getSingleScalarResult() > 0) {
            $this->context->buildViolation($this->translator->__('The user name you entered has already been registered.'))
                ->setParameter('%string%', $value)
                ->addViolation();
        }
    }
}