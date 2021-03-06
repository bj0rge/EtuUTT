<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class GrantCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:grant')
			->setDescription('Grant a role for an user')
		;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');

		$output->writeln('
	Welcome to the EtuUTT users grant tool

This command will help you to grant a role for a given user.

');

		$output->writeln('================== Current role hierarchy ==================');
		$output->writeln(print_r($this->getContainer()->getParameter('security.role_hierarchy.roles'),true));
		$output->writeln('================== End of current role hierarchy ==================');
		$output->writeln("\n");

		$user = null;

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		while (! $user instanceof User) {
			$login = $dialog->ask($output, 'User login: ');

			$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

			if (! $user) {
				$output->writeln("The given login can not be found. Please retry.\n");
			}
		}

		$role = $dialog->ask($output, 'Role: ');

		$user->storeRole($role);

		$em->persist($user);
		$em->flush();

		$output->writeln("The user ".$login." has been granted of role ".$role."\n");
	}
}
