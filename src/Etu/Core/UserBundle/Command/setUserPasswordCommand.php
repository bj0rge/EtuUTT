<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class setUserPasswordCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:set-password')
			->setDescription('Set user password to let him connect as an external account')
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
	Welcome to the EtuUTT users manager

This command will help you to set the password of an user to let him connect as an external account.
');

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
		$password = null;
		$confirm = null;

		while (! $password || $confirm != $password) {
			$password = $dialog->askHiddenResponse($output, 'Password: ', false);
			$confirm = $dialog->askHiddenResponse($output, 'Confirm password: ', false);

			if ($confirm != $password) {
				$output->writeln("Password and its confirmation are different. Please retry.\n");
			}
		}

		$password = $this->getContainer()->get('security.password_encoder')->encodePassword($user, $password);

		$user->setPassword($password);

		$em->persist($user);
		$em->flush();

		$output->writeln("The user ".$user->getLogin()." has can now connect with his password as external.\n");
	}
}
