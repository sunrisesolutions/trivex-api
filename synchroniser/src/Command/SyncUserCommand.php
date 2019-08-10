<?php

namespace App\Command;

use App\Entity\SyncLog;
use Doctrine\ORM\EntityRepository;
use phpDocumentor\Reflection\Types\Object_;
use PHPUnit\Runner\Exception;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SyncUserCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:sync:user';

    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $module = 'user';
        $clone = [
            [
                'src' => [
                    'connection' => 'person',
                    'entity' => \App\Entity\Person\Person::class,
                ],
                'des' => [
                    'connection' => 'user',
                    'entity' => \App\Entity\User\Person::class,
                ]
            ],
            [
                'src' => [
                    'connection' => 'person',
                    'entity' => \App\Entity\Person\Nationality::class,
                ],
                'des' => [
                    'connection' => 'user',
                    'entity' => \App\Entity\User\Nationality::class,
                ]
            ],
            [
                'src' => [
                    'connection' => 'organisation',
                    'entity' => \App\Entity\Organisation\Organisation::class,
                ],
                'des' => [
                    'connection' => 'user',
                    'entity' => \App\Entity\User\Organisation::class,
                ]
            ],
        ];

        $doctrine = $this->getContainer()->get('doctrine');
        $defaultEm = $this->getContainer()->get('doctrine.orm.default_entity_manager');

        $logRepo = $doctrine->getManager('default')->getRepository(SyncLog::class);
        $now = new \DateTime();
        $log = $logRepo->findOneBy(['module' => $module, 'createdOn' => $now]);
        if (empty($log)) {
            $log = $logRepo->findOneBy(['module' => $module], ['updatedAt' => 'DESC']);
            if (!empty($log) && $log->getStatus() === SyncLog::STATUS_RUNNING && (time() - $log->getUpdatedAt()->getTimestamp()) > 60*30) {
                $log->setStatus(SyncLog::STATUS_CANCELLED);

                $newLog = new SyncLog();
                $newLog->setModule($module);
                $newLog->setStatus(SyncLog::STATUS_RUNNING);
                $defaultEm->persist($newLog);

                foreach ($clone as $c) {
                    $output->write('Syncing ' . $c['src']['entity'] . ' -> ' . $c['des']['entity']);
                    $srcData = $doctrine->getManager($c['src']['connection'])->getRepository($c['src']['entity'])
                        ->createQueryBuilder('e')
                        ->where('e.updatedAt BETWEEN :from AND :to')
                        ->setParameter('from', $log->getUpdatedAt())
                        ->setParameter('to', $now)
                        ->getQuery()
                        ->getResult()
                    ;
                    $output->writeln(' ' . count($srcData) . ' results.');
                    if (count($srcData) > 0) {
                        $desEm = $doctrine->getManager($c['des']['connection']);
                        $des = $desEm->getRepository($c['des']['entity']);
                        /** @var App\Entity\Person\Person $source */
                        foreach ($srcData as $source) {
                            $output->writeln('Insert ' . $source->getUuid());
                            /** @var App\Entity\User\Person $desData */
                            $desData = $des->findOneBy(['uuid' => $source->getUuid()]);
                            if (empty($desData)) {
                                $desData = new $c['des']['entity']();
                            }

//                                $object = new \ReflectionObject($desData);
//                                foreach ($object->getMethods() as $method) {
//                                    $setter = $method->getName();
//                                    $getter = 'get' . substr($setter, 3);
//
//                                    if (method_exists($source, $getter) && method_exists($desData, $setter)) {
//                                        $paramType = null;
//                                        if (!empty($method->getParameters())) {
//                                            $paramType = $method->getParameters()[0]->getType()->getName();
//                                        }
//
//                                        if (in_array($paramType, ['string', 'integer', 'array', 'DateTimeInterface', 'bool', 'boolean'])) {
//                                            $desData->$setter($source->$getter());
//                                        } elseif ($paramType != null) {
//                                            var_dump($paramType);
//                                        }
//                                    }
//                                }

                            if ($c['src']['entity'] === \App\Entity\Person\Person::class) {
                                $desData->setBirthDate($source->getBirthDate());
                                $desData->setGivenName($source->getGivenName());
                                $desData->setFamilyName($source->getFamilyName());
                                $desData->setGender($source->getGender());
                                $desData->setEmail($source->getEmail());
                                $desData->setPhoneNumber($source->getPhoneNumber());
                                $desData->setUuid($source->getUuid());
                                $desData->setMiddleName($source->getMiddleName());
                                $desData->setJobTitle($source->getJobTitle());
                                $desData->setUserUuid($source->getUserUuid());
                                $desData->setCreatedAt($source->getUpdatedAt());
                                $us = $desEm->getRepository(\App\Entity\User\User::class)->findOneBy(['uuid' => $source->getUserUuid()]);
                                if (!empty($us)) {
                                    $desData->setUser($us);
                                }
                                $na = $source->getNationalities();
                                if (!empty($na)) {
                                    foreach ($na as $n) {
                                        $related = $desEm->getRepository(\App\Entity\User\Nationality::class)->findOneBy(['uuid' => $n->getUuid()]);
                                        if (empty($related)) {
                                            $related = new \App\Entity\User\Nationality();
                                        }
                                        $related->setCountry($n->getCountry());
                                        $related->setNricNumber($n->getNricNumber());
                                        $related->setPassportNumber($n->getPassportNumber());
                                        $related->setUuid($n->getUuid());
                                        $related->setPerson($desData);
                                        $desEm->persist($related);
                                        $desData->addNationality($related);
                                    }
                                }
                            } elseif ($c['src']['entity'] === \App\Entity\Person\Nationality::class) {
                                $desData->setCountry($source->getCountry());
                                $desData->setNricNumber($source->getNricNumber());
                                $desData->setPassportNumber($source->getPassportNumber());
                                $desData->setUuid($source->getUuid());
                                $pe = $source->getPerson();
                                if (!empty($pe)) {
                                    $related = $desEm->getRepository(\App\Entity\User\Person::class)->findOneBy(['uuid' => $pe->getUuid()]);
                                    if (empty($related)) {
                                        $related = new \App\Entity\User\Person();
                                    }
                                    $related->setBirthDate($pe->getBirthDate());
                                    $related->setGivenName($pe->getGivenName());
                                    $related->setFamilyName($pe->getFamilyName());
                                    $related->setGender($pe->getGender());
                                    $related->setEmail($pe->getEmail());
                                    $related->setPhoneNumber($pe->getPhoneNumber());
                                    $related->setUuid($pe->getUuid());
                                    $related->setMiddleName($pe->getMiddleName());
                                    $related->setJobTitle($pe->getJobTitle());
                                    $related->setUserUuid($pe->getUserUuid());
                                    $related->setCreatedAt($pe->getUpdatedAt());
                                    $related->addNationality($desData);
                                    $us = $desEm->getRepository(\App\Entity\User\User::class)->findOneBy(['uuid' => $pe->getUserUuid()]);
                                    if (!empty($us)) {
                                        $related->setUser($us);
                                    }
                                    $desEm->persist($related);
                                    $desData->setPerson($related);
                                }
                            } elseif ($c['src']['entity'] === \App\Entity\Organisation\Organisation::class) {

                                $desData->setUuid($source->getUuid());
                                $desData->setCode($source->getCode());
                                $members = $source->getIndividualMembers();
                                if (!empty($members)) {
                                    foreach ($members as $member) {
                                        $related = $desEm->getRepository(\App\Entity\User\OrganisationUser::class)->findOneBy(['uuid' => $member->getUuid()]);
                                        if (empty($related)) {
                                            $related = new \App\Entity\User\OrganisationUser();
                                        }
                                        $related->setUuid($member->getUuid());
                                        $related->setAccessToken($member->getAccessToken());
                                        $roles = $source->getRoles();
                                        $r = [];
                                        if (!empty($roles)) {

                                            foreach ($roles as $role) {
                                                $r[] = $role->getName();
                                            }
                                        }
                                        $related->setRoles($r);
                                        $personIm = $member->getPerson();
                                        if (!empty($personIm)) {
                                            $person = $doctrine->getManager('person')->getRepository(\App\Entity\Person\Person::class)->findOneBy(['uuid' => $personIm->getUuid()]);
                                            if (!empty($person)) {
                                                $us = $desEm->getRepository(\App\Entity\User\User::class)->findOneBy(['uuid' => $person->getUserUuid()]);
                                                if (!empty($us)) {
                                                    $related->setUser($us);
                                                }
                                            }
                                        }
                                        $related->setOrganisation($desData);
                                        $desEm->persist($related);
                                        $desData->addOrganisationUser($related);
                                    }
                                }
                            }
                            $desEm->persist($desData);
                            $desEm->flush();
                        }
                    }
                }
                $defaultEm->flush();
            } elseif (!empty($log)) {
                $io->note('Module:' . $module . ' Status:' . $log->getStatus() . ' Updated: ' . gmdate("H:i:s", (time() - $log->getUpdatedAt()->getTimestamp())) . ' ago.');
            } else {
                $io->warning('Undefined last sync log module: ' . $module);
            }
        } else {
            $io->success('Already up to date');
        }
    }
}
