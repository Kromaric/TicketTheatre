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
import { Link as RouterLink, useNavigate } from "react-router-dom";

export default function Header() {
  const navigate = useNavigate();

  return (
    <Box
      as="header"
      w="full"
      position="sticky"
      top="0"
      p="4"
      zIndex="1000"
      bg="rgba(0, 0, 0, 0.65)"  
      backdropFilter="blur(8px)"  
      boxShadow="sm"
    >
      <Flex alignItems="center" justifyContent="space-between" w="full">
        <Box p="2">
          <Link asChild _hover={{ textDecoration: "none" }}>
            <RouterLink to="/">
              <Heading size="md">Le Velum</Heading>
            </RouterLink>
          </Link>
        </Box>

        {/* Desktop */}
        <Box bg="red.800" borderRadius="full" display="none" sm={{ display: "block" }}>
          <Flex alignItems="center" color="white" gap="4">
            <Link
              asChild
              p="2"
              _hover={{ textDecoration: "none", bg: "red.700", borderRadius: "full" }}
            >
              <RouterLink to="/connexion">Connexion</RouterLink>
            </Link>

            <Link
              asChild
              p="2"
              _hover={{ textDecoration: "none", bg: "red.700", borderRadius: "full" }}
            >
              <RouterLink to="/programme">Le Programme</RouterLink>
            </Link>

            <Link
              asChild
              p="2"
              _hover={{ textDecoration: "none", bg: "red.700", borderRadius: "full" }}
            >
              <RouterLink to="/theatre">Le Théatre</RouterLink>
            </Link>
          </Flex>
        </Box>

        {/* Mobile */}
        <Box sm={{ display: "none" }} display="block">
          <Menu.Root>
            <Menu.Trigger asChild>
              <Button
                variant="outline"
                size="md"
                borderRadius="full"
                bg="red.800"
                color="white"
                _hover={{ bg: "red.700" }}
              >
                Menu
                <Icon>
                  <BiMenu />
                </Icon>
              </Button>
            </Menu.Trigger>

            <Portal>
              <Menu.Positioner>
                <Menu.Content bg="red.800" color="white">
                  <Menu.Item value="connexion" onClick={() => navigate("/connexion")}>
                    Connexion
                  </Menu.Item>
                  <Menu.Item value="programme" onClick={() => navigate("/programme")}>
                    Le Programme
                  </Menu.Item>
                  <Menu.Item value="theatre" onClick={() => navigate("/theatre")}>
                    Le Théatre
                  </Menu.Item>
                </Menu.Content>
              </Menu.Positioner>
            </Portal>
          </Menu.Root>
        </Box>
      </Flex>
    </Box>
  );
}
