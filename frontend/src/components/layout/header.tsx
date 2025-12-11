import {
  Box,
  Flex,
  Heading,
  Link,
  Menu,
  Button,
  Portal,
  Icon,
} from "@chakra-ui/react";
import { BiMenu } from "react-icons/bi";

export default function Header() {
  return (
    <Box as="header" w="full" position="sticky" top="0" p="4" zIndex="1000">
      <Flex
        alignItems="center"
        justifyContent="space-between"
        flexDirection="row"
        w="full"
      >
        <Box p="2">
          <Heading size="md">Le Velum</Heading>
        </Box>
        <Box
          bg="red.800"
          borderRadius="full"
          display="none"
          sm={{ display: "block" }}
        >
          <Flex alignItems="center" color="white" gap="4">
            <Link
              href="/"
              p="2"
              _hover={{
                textDecoration: "none",
                bg: "red.700",
                borderRadius: "full",
              }}
            >
              Connexion
            </Link>
            <Link
              href="/services"
              p="2"
              _hover={{
                textDecoration: "none",
                bg: "red.700",
                borderRadius: "full",
              }}
            >
              Le Programme
            </Link>
            <Link
              href="/services"
              p="2"
              _hover={{
                textDecoration: "none",
                bg: "red.700",
                borderRadius: "full",
              }}
            >
              Le Théatre
            </Link>
          </Flex>
        </Box>

        <Box sm={{ display: "none" }} display="block">
          <Menu.Root>
            <Menu.Trigger asChild>
              <Button
                variant="outline"
                size="md"
                borderRadius="full"
                bg="red.800"
                color="white"
              >
                Menu
                <Icon>
                  <BiMenu />
                </Icon>
              </Button>
            </Menu.Trigger>
            <Portal>
              <Menu.Positioner>
                <Menu.Content bg="red.800">
                  <Menu.Item value="new-txt">Connexion</Menu.Item>
                  <Menu.Item value="new-file">Le Programme</Menu.Item>
                  <Menu.Item value="new-win">Le Théatre</Menu.Item>
                </Menu.Content>
              </Menu.Positioner>
            </Portal>
          </Menu.Root>
        </Box>
      </Flex>
    </Box>
  );
}
