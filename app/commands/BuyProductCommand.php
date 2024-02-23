<?php
declare(strict_types=1);

namespace app\commands;

use AllowDynamicProperties;
use Symfony\Component\Console\{Attribute\AsCommand,
    Command\Command,
    Input\InputInterface,
    Output\OutputInterface,
    Question\Question};
/**
 *
 */
#[AllowDynamicProperties] #[AsCommand(
    name: 'app:buy-product',
    description: 'Select product first then pay in coins',
)]
class BuyProductCommand extends Command
{
    /**
     * @param array $products
     * @param array $coins
     */
    public function __construct(array $products, array $coins)
    {
        parent::__construct();
        $this->products = $products;
        $this->coins = $coins;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        try {
            $this->showProducts($output);
            $productName = $this->selectProduct($input, $output);
            if (!$this->isValidProduct($productName)) {
                $output->writeln("Product not found.");
                return Command::FAILURE;
            }
            $this->processPayment($input, $output, $productName);
            return Command::SUCCESS;
        } catch (\Exception $e) {
            return  Command::FAILURE;
        }
    }

    /**
     * @param OutputInterface $output
     * @return void
     */
    private function showProducts(OutputInterface $output): void
    {
        $output->writeln("Available products:");
        foreach ($this->products as $product => $price) {

            $output->writeln("$product - " . (float)$price);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    private function selectProduct(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');
        $question = new Question("Enter the name of the product: ");
        return $helper->ask($input, $output, $question);
    }

    /**
     * @param string $productName
     * @return bool
     */
    private function isValidProduct(string $productName): bool
    {
        return array_key_exists($productName, $this->products);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $productName
     * @return void
     */
    private function processPayment(InputInterface $input, OutputInterface $output, string $productName): void
    {
        $output->writeln("Your choice: $productName. Price: " . (float)$this->products[$productName]);
        $output->writeln("Insert coins:");
        $enteredCoins = 0.0;
        while ($enteredCoins < $this->products[$productName]) {
            $enteredCoins += $this->promptForCoins($input, $output, $productName, $enteredCoins);
        }

        $output->writeln("Product dispensed. Your change: " . ($enteredCoins - (float)$this->products[$productName]));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $productName
     * @param float $enteredCoins
     * @return float
     */
    private function promptForCoins(InputInterface $input, OutputInterface $output, string $productName, float $enteredCoins): float
    {
        $remaining = $this->products[$productName] - $enteredCoins;
        $helper = $this->getHelper('question');
        $question = new Question("Remaining amount: $remaining\n");
        $coin = floatval($helper->ask($input, $output, $question));

        if (!in_array($coin, $this->coins)) {
            $output->writeln("Invalid coin. Try again. Available ones are " . implode(',', $this->coins));
            return 0;
        }
        return $coin;
    }
}