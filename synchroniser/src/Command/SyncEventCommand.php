<?php

namespace App\Command;

use App\Entity\SyncLog;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class SyncEventCommand extends ContainerAwareCommand
{
    protected static $defaultName = 'app:sync:event';

    protected function configure()
    {
        $this->setDescription('Add a short description for your command');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $module = 'event';
        $clone = [
            [
                'src' => [
                    'connection' => 'person',
                    'entity' => \App\Entity\Person\Person::class,
                ],
                'des' => [
                    'connection' => 'event',
                    'entity' => \App\Entity\Event\Person::class,
                ]
            ],
            [
                'src' => [
                    'connection' => 'organisation',
                    'entity' => \App\Entity\Organisation\Organisation::class,
                ],
                'des' => [
                    'connection' => 'event',
                    'entity' => \App\Entity\Event\Organisation::class,
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
                        foreach ($srcData as $source) {
                            $desData = $des->findOneBy(['uuid' => $source->getUuid()]);
                            if (empty($desData)) {
                                $output->writeln('Insert ' . $source->getUuid());
                                $desData = new $c['des']['entity']();
                            } else {
                                $output->writeln('Update ' . $source->getUuid());
                            }

                            if ($c['src']['entity'] === \App\Entity\Person\Person::class) {
                                $desData->setUuid($source->getUuid());
                                $desData->setEmail($source->getEmail());
                                $desData->setBirthDate($source->getBirthDate());
                                $desData->setPhoneNumber($source->getPhoneNumber());
                            } elseif ($c['src']['entity'] === \App\Entity\Organisation\Organisation::class) {
                                $desData->setUuid($source->getUuid());
                                $desData->setCode($source->getCode());
                                $ims = $source->getIndividualMembers();
                                if (!empty($ims)) {
                                    foreach ($ims as $im) {
                                        $relatedIm = $desEm->getRepository(\App\Entity\Event\IndividualMember::class)->findOneBy(['uuid' => $im->getUuid()]);
                                        if (empty($relatedIm)) {
                                            $output->writeln('Insert ' . $im->getUuid());
                                            $relatedIm = new \App\Entity\Event\IndividualMember();
                                        } else {
                                            $output->writeln('Update ' . $im->getUuid());
                                        }
                                        $relatedIm->setUuid($im->getUuid());
                                        $relatedIm->setOrganisation($desData);
                                        $person = $im->getPerson();
                                        if (!empty($person)) {
                                            $relatedPe = $desEm->getRepository(\App\Entity\Event\Person::class)->findOneBy(['uuid' => $person->getUuid()]);
                                            if (!empty($relatedPe)) {
                                                $output->writeln('Update ' . $person->getUuid());
                                                $relatedIm->setPerson($relatedPe);
                                                $relatedPe->addIndividualMember($relatedIm);
                                                $desEm->persist($relatedPe);
                                            }
                                        }
                                        $desEm->persist($relatedIm);
                                        $desData->addIndividualMember($relatedIm);
                                    }
                                }
                            }
                            $desEm->persist($desData);
                        }
                        $desEm->flush();
                    }
                }
                $defaultEm->flush();
            } elseif (!empty($log)) {
                $io->note('Module:' . $module . ' Status:' . $log->getStatus() . ' Updated: ' . gmdate("H:i:s", (time() - $log->getUpdatedAt()->getTimestamp())) . ' ago.');
            } else {
                $io->warning('Undefined last sync log module: ' . $module);
                if ($this->getHelper('question')->ask($input, $output, new ConfirmationQuestion('create new one ?', false)) === true) {
                    $lastTs = new \DateTime(date('Y-m-d H:i:s', strtotime('-1 month')));
                    $last = new SyncLog();
                    $last->setModule($module);
                    $last->setStatus(SyncLog::STATUS_RUNNING);
                    $last->setCreatedOn($lastTs);
                    $last->setUpdatedAt($lastTs);
                    $defaultEm->persist($last);
                    $defaultEm->flush();
                    $output->writeln('done');
                }
            }
        } else {
            $io->success('Already up to date');
        }
    }
}
